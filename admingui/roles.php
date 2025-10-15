<?php

/**
 * @package modules\workflow
 * @category Xaraya Web Applications Framework
 * @version 2.5.7
 * @copyright see the html/credits.html file in this release
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link https://github.com/mikespub/xaraya-modules
**/

namespace Xaraya\Modules\Workflow\AdminGui;

use Xaraya\Modules\Workflow\AdminGui;
use Xaraya\Modules\MethodClass;
use sys;

sys::import('xaraya.modules.method');

/**
 * workflow admin roles function
 * @extends MethodClass<AdminGui>
 */
class RolesMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * the roles administration function
     * @author mikespub
     * @access public
     * @see AdminGui::roles()
     */
    public function __invoke(array $args = [])
    {
        // Security Check
        if (!$this->sec()->checkAccess('AdminWorkflow')) {
            return;
        }

        // Common setup for Galaxia environment
        sys::import('modules.workflow.lib.galaxia.config');
        $data = [];
        $maxRecords = $this->mod()->getVar('items_per_page');

        // Adapted from tiki-g-admin_roles.php
        include_once(GALAXIA_LIBRARY . '/processmanager.php');

        $this->var()->find('pid', $pid, 'id');
        if (empty($pid)) {
            $data['msg'] =  $this->ml("No process indicated");
            $data['context'] ??= $this->getContext();
            return $this->mod()->template('errors', $data);
        }
        $data['pid'] =  $pid;

        // Retrieve the relevant process info
        $process = new \Galaxia\Api\Process($pid);
        $proc_info = $processManager->get_process($pid);
        $proc_info['graph'] = $process->getGraph();

        // Role ID set?
        $this->var()->find('roleId', $roleId, 'id', 0);
        if ($roleId) {
            // Get it
            $data['info'] = $roleManager->get_role($pid, $roleId);
        } else {
            // Set it
            $data['info'] = ['name' => '', 'description' => '', 'roleId' => 0 ];
        }
        $data['roleId'] =  $roleId;

        // Delete roles
        if (isset($_REQUEST["delete"])) {
            foreach (array_keys($_REQUEST["role"]) as $item) {
                $roleManager->remove_role($pid, $item);
            }
        }

        // If we are adding an roles then add it!
        if (isset($_REQUEST['save'])) {
            $vars = ['name' => $_REQUEST['name'],'description' => $_REQUEST['description']];
            // Save that
            $roleManager->replace_role($pid, $roleId, $vars);
            $vars['roleId'] = $roleId;
            $data['info'] =  $vars;
        }

        // MAPPING
        $data['find_users'] = $_REQUEST['find_users'] ?? '';

        $numusers = $this->mod()->apiFunc('roles', 'user', 'countall');
        // don't show thousands of users here without filtering
        if ($numusers > 1000 && empty($data['find_users'])) {
            $data['users'] = [];
        } else {
            $selection = '';
            if (!empty($data['find_users'])) {
                $dbconn = $this->db()->getConn();
                $selection = " AND name LIKE " . $dbconn->qstr('%' . $data['find_users'] . '%');
            }
            $data['users'] = $this->mod()->apiFunc(
                'roles',
                'user',
                'getall',
                ['selection' => $selection,
                    'order' => 'name', ]
            );
        }

        $data['groups'] = $this->mod()->apiFunc('roles', 'user', 'getallgroups');

        $roles = $roleManager->list_roles($pid, 0, -1, 'name_asc', '');
        $data['roles'] = &$roles['data'];

        if (isset($_REQUEST["delete_map"])) {
            foreach (array_keys($_REQUEST["map"]) as $item) {
                $parts = explode(':::', $item);
                $roleManager->remove_mapping($parts[0], $parts[1]);
            }
        }

        if (isset($_REQUEST['mapg'])) {
            if ($_REQUEST['op'] == 'add') {
                $users = $this->mod()->apiFunc(
                    'roles',
                    'user',
                    'getusers',
                    ['id' => $_REQUEST['group']]
                );
                foreach ($users as $a_user) {
                    $roleManager->map_user_to_role($pid, $a_user['id'], $_REQUEST['role']);
                }
            } else {
                $users = $this->mod()->apiFunc(
                    'roles',
                    'user',
                    'getusers',
                    ['id' => $_REQUEST['group']]
                );
                foreach ($users as $a_user) {
                    $roleManager->remove_mapping($a_user['id'], $_REQUEST['role']);
                }
            }
        }

        if (isset($_REQUEST['save_map'])) {
            if (isset($_REQUEST['user']) && isset($_REQUEST['role'])) {
                foreach ($_REQUEST['user'] as $a_user) {
                    if (empty($a_user)) {
                        $a_user = _XAR_ID_UNREGISTERED;
                    }
                    foreach ($_REQUEST['role'] as $role) {
                        $roleManager->map_user_to_role($pid, $a_user, $role);
                    }
                }
            }
        }

        // list mappings
        $data['offset']    = $_REQUEST['offset'] ?? 1;
        $data['find']      = $_REQUEST['find'] ?? '';
        $data['sort_mode'] = $_REQUEST['sort_mode'] ?? 'name_asc';
        $mapitems = $roleManager->list_mappings($pid, $data['offset'] - 1, $maxRecords, $data['sort_mode'], $data['find']);

        // trick : replace userid by user here !
        foreach (array_keys($mapitems['data']) as $index) {
            $role = $this->mod()->apiFunc(
                'roles',
                'user',
                'get',
                ['id' => $mapitems['data'][$index]['user']]
            );
            if (!empty($role)) {
                $mapitems['data'][$index]['userId'] = $role['id'];
                $mapitems['data'][$index]['user'] = $role['name'];
                $mapitems['data'][$index]['login'] = $role['uname'];
            } else {
                $roleManager->remove_mapping($mapitems['data'][$index]['user'], $mapitems['data'][$index]['roleId']);
                $mapitems['cant'] = $mapitems['cant'] - 1;
                unset($mapitems['data'][$index]);
            }
        }

        $data['cant'] =  $mapitems['cant'];
        $data['cant_pages']  =  ceil($mapitems["cant"] / $maxRecords);
        $data['actual_page'] =  1 + (($data['offset'] - 1) / $maxRecords);

        $data['next_offset'] =  -1;
        if ($mapitems["cant"] >= ($data['offset'] + $maxRecords)) {
            $data['next_offset'] =  $data['offset'] + $maxRecords;
        }

        $data['prev_offset'] =  -1;
        if ($data['offset'] > 1) {
            $data['prev_offset'] =  $data['offset'] - $maxRecords;
        }
        $data['mapitems'] = &$mapitems["data"];

        //MAPPING
        $data['sort_mode2'] =  $_REQUEST['sort_mode2'] ?? 'name_asc';
        // Get all the process roles
        $all_roles = $roleManager->list_roles($pid, 0, -1, $data['sort_mode2'], '');
        $data['items'] = &$all_roles['data'];

        $valid = $activityManager->validate_process_activities($pid);
        $proc_info['isValid'] = $valid ? 1 : 0;
        $errors = [];
        if (!$valid) {
            $errors = $activityManager->get_error();
        }

        $data['errors'] =  $errors;
        $data['proc_info'] =  $proc_info;

        // $data['pager'] = $this->tpl()->getPager($data['offset'],$mapitems['cant'],$url,$maxRecords);
        $data['url'] = $this->mod()->getURL('admin', 'roles', ['pid' => $data['pid'],'offset' => '%%']);
        $data['maxRecords'] = $maxRecords;
        return $data;
    }
}
