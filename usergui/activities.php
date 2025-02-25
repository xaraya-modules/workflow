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
use xarSecurity;
use xarUser;
use xarModVars;
use xarController;
use xarTplPager;
use xarServer;
use sys;
use BadParameterException;

sys::import('xaraya.modules.method');

/**
 * workflow user activities function
 * @extends MethodClass<UserGui>
 */
class ActivitiesMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * the activities user function
     * @author mikespub
     * @access public
     * @see UserGui::activities()
     */
    public function __invoke(array $args = [])
    {
        // Security Check
        if (!$this->sec()->checkAccess('ReadWorkflow')) {
            return;
        }

        // Common setup for Galaxia environment
        sys::import('modules.workflow.lib.galaxia.config');
        $data = [];

        // Adapted from tiki-g-user_activities.php
        include_once(GALAXIA_LIBRARY . '/gui.php');

        // Initialize some stuff
        $user = $this->user()->getId();
        $maxRecords = $this->mod()->getVar('items_per_page');

        // Filtering data to be received by request and
        // used to build the where part of a query
        // filter_active, filter_valid, find, sort_mode,
        // filter_process
        $where = '';
        $wheres = [];

        /*
        if(isset($_REQUEST['filter_active'])&&$_REQUEST['filter_active']) $wheres[]="isActive='".$_REQUEST['filter_active']."'";
        if(isset($_REQUEST['filter_valid'])&&$_REQUEST['filter_valid']) $wheres[]="isValid='".$_REQUEST['filter_valid']."'";
        */
        if (isset($_REQUEST['filter_process']) && $_REQUEST['filter_process']) {
            $wheres[] = "gp.pId=" . $_REQUEST['filter_process'] . "";
        }

        $where = implode(' and ', $wheres);

        if (!isset($_REQUEST["sort_mode"])) {
            $sort_mode = 'pId_asc, flowNum_asc';
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

        $items = $GUI->gui_list_user_activities($user, $offset - 1, $maxRecords, $sort_mode, $find, $where);
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
        if (count($data['all_procs']) == 1 && empty($_REQUEST['filter_process'])) {
            $_REQUEST['filter_process'] = $data['all_procs'][0]['pId'];
        }

        if (isset($_REQUEST['filter_process']) && $_REQUEST['filter_process']) {
            $actid2item = [];
            foreach (array_keys($data['items']) as $index) {
                $actid2item[$data['items'][$index]['activityId']] = $index;
            }
            foreach ($data['all_procs'] as $info) {
                if ($info['pId'] == $_REQUEST['filter_process'] && !empty($info['normalized_name'])) {
                    $graph = GALAXIA_PROCESSES . "/" . $info['normalized_name'] . "/graph/" . $info['normalized_name'] . ".png";
                    $mapfile = GALAXIA_PROCESSES . "/" . $info['normalized_name'] . "/graph/" . $info['normalized_name'] . ".map";
                    if (file_exists($graph) && file_exists($mapfile)) {
                        $maplines = file($mapfile);
                        $map = '';
                        foreach ($maplines as $mapline) {
                            if (!preg_match('/activityId=(\d+)/', $mapline, $matches)) {
                                continue;
                            }
                            $actid = $matches[1];
                            if (!isset($actid2item[$actid])) {
                                continue;
                            }
                            $index = $actid2item[$actid];
                            $item = $data['items'][$index];
                            if ($item['instances'] > 0) {
                                $url = $this->mod()->getURL(
                                    'user',
                                    'instances',
                                    ['filter_process' => $info['pId']]
                                );
                                $mapline = preg_replace('/href=".*?activityId/', 'href="' . $url . '&amp;filter_activity', $mapline);
                                $map .= $mapline;
                            } elseif ($item['isInteractive'] && ($item['type'] == 'start' || $item['type'] == 'standalone')) {
                                $url = $this->mod()->getURL('user', 'run_activity');
                                $mapline = preg_replace('/href=".*?activityId/', 'href="' . $url . '&amp;activityId', $mapline);
                                $map .= $mapline;
                            }
                        }
                        // Darn graphviz does not close the area tags
                        $map = preg_replace('#<area (.*[^/])>#', '<area $1/>', $map);

                        $data['graph'] = $graph;
                        $data['map'] = $map;
                        $data['procname'] = $info['procname'];
                    } else {
                        $data['graph'] = '';
                    }
                    break;
                }
            }
        }

        //$section = 'workflow';
        //include_once ('tiki-section_options.php');
        $sameurl_elements = [
            'offset',
            'sort_mode',
            'where',
            'find',
            'filter_isInteractive',
            'filter_isAutoRouted',
            'filter_activity',
            'filter_type',
            'processId',
            'filter_process',
        ];

        $data['mid'] =  'tiki-g-user_activities.tpl';

        // Missing variable
        $data['filter_process'] = $_REQUEST['filter_process'] ?? '';

        /*        $data['pager'] = $this->tpl()->getPager($data['offset'],
                                                   $items['cant'],
                                                   $url,
                                                   $maxRecords);*/
        $data['maxRecords'] = $maxRecords;
        $data['url'] = $this->ctl()->getCurrentURL(['offset' => '%%']);
        return $data;
    }
}
