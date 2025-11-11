<?php

/**
 * @package modules\workflow
 * @category Xaraya Web Applications Framework
 * @version 2.5.7
 * @copyright see the html/credits.html file in this release
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link https://github.com/mikespub/xaraya-modules
**/

namespace Xaraya\Modules\Workflow\UserGui;

use Xaraya\Modules\Workflow\UserGui;
use Xaraya\Modules\MethodClass;

/**
 * workflow user instances function
 * @extends MethodClass<UserGui>
 */
class InstancesMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * the instances user function
     * @author mikespub
     * @access public
     * @see UserGui::instances()
     */
    public function __invoke(array $args = [])
    {
        // Security Check
        if (!$this->sec()->checkAccess('ReadWorkflow')) {
            return;
        }
        /** @var UserGui $usergui */
        $usergui = $this->usergui();

        // Initialize some stuff
        $user = $this->user()->getId();
        $maxRecords = $this->mod()->getVar('items_per_page');

        if (isset($_REQUEST['run']) || isset($_REQUEST['run_x'])) {
            return $usergui->runActivity();
        }

        if (isset($_REQUEST['remove']) || isset($_REQUEST['remove_x'])) {
            $this->var()->find('iid', $iid);
            $this->var()->find('return_url', $return_url);
            if (!empty($iid)) {
                if ($this->user()->isLoggedIn()) {
                    $seenlist = $this->mod()->getUserVar('seenlist');
                    if (empty($seenlist)) {
                        $this->mod()->setUserVar('seenlist', $iid);
                    } else {
                        $this->mod()->setUserVar('seenlist', $seenlist . ';' . $iid);
                    }
                } else {
                    $seenlist = $this->session()->getVar('workflow.seenlist');
                    if (empty($seenlist)) {
                        $this->session()->setVar('workflow.seenlist', $iid);
                    } else {
                        $this->session()->setVar('workflow.seenlist', $seenlist . ';' . $iid);
                    }
                }
                if (!empty($return_url)) {
                    $this->ctl()->redirect($return_url);
                    return true;
                }
            }
        }

        // Common setup for Galaxia environment
        require_once dirname(__DIR__) . '/lib/galaxia/config.php';
        $data = [];

        // Adapted from tiki-g-user_instances.php

        include_once(\GALAXIA_LIBRARY . '/gui.php');

        $action = 0;

        // Filtering data to be received by request and
        // used to build the where part of a query
        // filter_active, filter_valid, find, sort_mode,
        // filter_process
        if (isset($_REQUEST['send']) || isset($_REQUEST['send_x'])) {
            $GUI->gui_send_instance($user, $_REQUEST['aid'], $_REQUEST['iid']);
            $action = 1;
        } elseif (isset($_REQUEST['abort']) || isset($_REQUEST['abort_x'])) {
            $GUI->gui_abort_instance($user, $_REQUEST['aid'], $_REQUEST['iid']);
            $action = 1;
        } elseif (isset($_REQUEST['exception']) || isset($_REQUEST['exception_x'])) {
            $GUI->gui_exception_instance($user, $_REQUEST['aid'], $_REQUEST['iid']);
            $action = 1;
        } elseif (isset($_REQUEST['resume']) || isset($_REQUEST['resume_x'])) {
            $GUI->gui_resume_instance($user, $_REQUEST['aid'], $_REQUEST['iid']);
            $action = 1;
        } elseif (isset($_REQUEST['grab']) || isset($_REQUEST['grab_x'])) {
            $GUI->gui_grab_instance($user, $_REQUEST['aid'], $_REQUEST['iid']);
            $action = 1;
        } elseif (isset($_REQUEST['release']) || isset($_REQUEST['release_x'])) {
            $GUI->gui_release_instance($user, $_REQUEST['aid'], $_REQUEST['iid']);
            $action = 1;
        }

        if ($action && !empty($_REQUEST['return_url'])) {
            $this->ctl()->redirect($_REQUEST['return_url']);
            return true;
        }

        $where = '';
        $wheres = [];

        if (isset($_REQUEST['filter_status']) && $_REQUEST['filter_status']) {
            $wheres[] = "gi.status='" . $_REQUEST['filter_status'] . "'";
        }

        if (isset($_REQUEST['filter_act_status']) && $_REQUEST['filter_act_status']) {
            $wheres[] = "gia.status='" . $_REQUEST['filter_act_status'] . "'";
        }

        if (isset($_REQUEST['filter_process']) && $_REQUEST['filter_process']) {
            $wheres[] = "gi.pId=" . $_REQUEST['filter_process'] . "";
        }

        if (isset($_REQUEST['filter_activity']) && $_REQUEST['filter_activity']) {
            $wheres[] = "gia.activityId=" . $_REQUEST['filter_activity'] . "";
        }

        if (isset($_REQUEST['filter_user']) && $_REQUEST['filter_user']) {
            $wheres[] = "gia.user='" . $_REQUEST['filter_user'] . "'";
        }

        if (isset($_REQUEST['filter_owner']) && $_REQUEST['filter_owner']) {
            $wheres[] = "owner='" . $_REQUEST['filter_owner'] . "'";
        }

        $where = implode(' and ', $wheres);

        if (!isset($_REQUEST["sort_mode"])) {
            $sort_mode = 'pId_asc, instanceId_asc';
        } else {
            $sort_mode = $_REQUEST["sort_mode"];
        }

        if (!isset($_REQUEST["offset"])) {
            $offset = 1;
        } else {
            $offset = $_REQUEST["offset"];
        }

        $data['offset'] = &$offset;

        if (isset($_REQUEST["find"])) {
            $find = $_REQUEST["find"];
        } else {
            $find = '';
        }

        $data['find'] =  $find;
        $data['where'] =  $where;
        $data['sort_mode'] = &$sort_mode;

        $items = $GUI->gui_list_user_instances($user, $offset - 1, $maxRecords, $sort_mode, $find, $where);
        $data['cant'] =  $items['cant'];

        $cant_pages = ceil($items["cant"] / $maxRecords);
        $data['cant_pages'] = &$cant_pages;
        $data['actual_page'] =  1 + (($offset - 1) / $maxRecords);

        if ($items["cant"] >= ($offset + $maxRecords)) {
            $data['next_offset'] =  $offset + $maxRecords;
        } else {
            $data['next_offset'] =  -1;
        }

        if ($offset > 1) {
            $data['prev_offset'] =  $offset - $maxRecords;
        } else {
            $data['prev_offset'] =  -1;
        }

        $data['items'] = &$items["data"];

        $processes = $GUI->gui_list_user_processes($user, 0, -1, 'procname_asc', '', '');
        $data['all_procs'] = &$processes['data'];

        $all_statuses = [
            'aborted',
            'active',
            'exception',
        ];

        $data['statuses'] =  $all_statuses;

        //$section = 'workflow';
        //include_once ('tiki-section_options.php');

        $sameurl_elements = [
            'offset',
            'sort_mode',
            'where',
            'find',
            'filter_user',
            'filter_status',
            'filter_act_status',
            'filter_type',
            'processId',
            'filter_process',
            'filter_owner',
            'filter_activity',
        ];

        $data['mid'] =  'tiki-g-user_instances.tpl';

        // Missing variables
        $data['filter_process'] = $_REQUEST['filter_process'] ?? '';
        $data['filter_status'] = $_REQUEST['filter_status'] ?? '';
        $data['filter_act_status'] = $_REQUEST['filter_act_status'] ?? '';
        $data['filter_user'] = $_REQUEST['filter_user'] ?? '';
        $data['userId'] = $user;
        $data['user'] = $this->user($user)->getName();

        /*    $data['pager'] = $this->tpl()->getPager($data['offset'],
                                               $items['cant'],
                                               $url,
                                               $maxRecords);*/
        $data['url'] = $this->ctl()->getCurrentURL(['offset' => '%%']);
        $data['maxRecords'] = $maxRecords;
        return $data;
    }
}
