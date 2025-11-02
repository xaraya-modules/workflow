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
 * workflow admin instance function
 * @extends MethodClass<AdminGui>
 */
class InstanceMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * the instance administration function
     * @author mikespub
     * @access public
     * @see AdminGui::instance()
     */
    public function __invoke(array $args = [])
    {
        // Security Check
        if (!$this->sec()->checkAccess('AdminWorkflow')) {
            return;
        }

        // Common setup for Galaxia environment
        require_once dirname(__DIR__) . '/lib/galaxia/config.php';
        $tplData = [];

        // Adapted from tiki-g-admin_instance.php

        include_once(\GALAXIA_LIBRARY . '/processmanager.php');
        include_once(\GALAXIA_LIBRARY . '/api.php');

        if (!isset($_REQUEST['iid'])) {
            $tplData['msg'] =  $this->ml("No instance indicated");
            $tplData['context'] ??= $this->getContext();
            return $this->mod()->template('errors', $tplData);
        }
        $tplData['iid'] =  $_REQUEST['iid'];

        // Get workitems and list the workitems with an option to edit workitems for
        // this instance
        if (isset($_REQUEST['save'])) {
            //status, owner
            $instanceManager->set_instance_status($_REQUEST['iid'], $_REQUEST['status']);

            $instanceManager->set_instance_owner($_REQUEST['iid'], $_REQUEST['owner']);
            //y luego acts[activityId][user] para reasignar users
            foreach (array_keys($_REQUEST['acts']) as $act) {
                $instanceManager->set_instance_user($_REQUEST['iid'], $act, $_REQUEST['acts'][$act]);
            }

            if ($_REQUEST['sendto']) {
                $instanceManager->set_instance_destination($_REQUEST['iid'], $_REQUEST['sendto']);
            }
            //process sendto
        }

        // Get the instance and set instance information
        $ins_info = $instanceManager->get_instance($_REQUEST['iid']);
        if (!empty($ins_info['started'])) {
            $date = $this->mls()->getFormattedDate('medium', $ins_info['started']) . ' '
                    . $this->mls()->getFormattedTime('short', $ins_info['started']);
            $ins_info['started'] = $date;
        }
        $tplData['ins_info'] = &$ins_info;

        // Get the process from the instance and set information
        // @todo use the $process object itself
        $proc_info = $processManager->get_process($ins_info['pId']);
        $tplData['proc_info'] = &$proc_info;

        // Process activities
        $activities = $activityManager->list_activities($ins_info['pId'], 0, -1, 'flowNum_asc', '', '');
        $tplData['activities'] =  $activities['data'];

        // Users
        $mapitems = $roleManager->list_mappings($ins_info['pId'], 0, -1, 'name_asc', '');
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
            }
        }
        $tplData['users'] = &$mapitems['data'];

        $props = $instanceManager->get_instance_properties($_REQUEST['iid']);

        if (isset($_REQUEST['unsetprop'])) {
            unset($props[$_REQUEST['unsetprop']]);

            $instanceManager->set_instance_properties($_REQUEST['iid'], $props);
        }

        if (!is_array($props)) {
            $props = [];
        }

        $tplData['props'] = &$props;

        if (isset($_REQUEST['addprop'])) {
            $props[$_REQUEST['name']] = $_REQUEST['value'];

            $instanceManager->set_instance_properties($_REQUEST['iid'], $props);
        }

        if (isset($_REQUEST['saveprops'])) {
            foreach (array_keys($_REQUEST['props']) as $key) {
                $props[$key] = $_REQUEST['props'][$key];
            }

            $instanceManager->set_instance_properties($_REQUEST['iid'], $props);
        }

        $acts = $instanceManager->get_instance_activities($_REQUEST['iid']);
        $tplData['acts'] = &$acts;

        $instance = new \Galaxia\Api\Instance();
        $instance->getInstance($_REQUEST['iid']);

        // Process comments
        if (isset($_REQUEST['__removecomment'])) {
            $__comment = $instance->get_instance_comment($_REQUEST['__removecomment']);

            if ($__comment['user'] == $user) {
                $instance->remove_instance_comment($_REQUEST['__removecomment']);
            }
        }

        $tplData['__comments'] = &$__comments;

        if (!isset($_REQUEST['__cid'])) {
            $_REQUEST['__cid'] = 0;
        }

        if (isset($_REQUEST['__post'])) {
            $instance->replace_instance_comment($_REQUEST['__cid'], 0, '', $user, $_REQUEST['__title'], $_REQUEST['__comment']);
        }

        $__comments = $instance->get_instance_comments();

        $tplData['mid'] =  'tiki-g-admin_instance.tpl';

        return $tplData;
    }
}
