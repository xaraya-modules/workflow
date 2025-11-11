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
use Xaraya\Modules\Workflow\UserApi;
use Xaraya\Modules\MethodClass;

/**
 * workflow admin monitor_activities function
 * @extends MethodClass<AdminGui>
 */
class MonitorActivitiesMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * the monitor activities administration function
     * @author mikespub
     * @access public
     * @see AdminGui::monitorActivities()
     */
    public function __invoke(array $args = [])
    {
        /** @var UserApi $userapi */
        $userapi = $this->userapi();
        // Security Check
        if (!$this->sec()->checkAccess('AdminWorkflow')) {
            return;
        }

        // Common setup for Galaxia environment
        require_once dirname(__DIR__) . '/lib/galaxia/config.php';
        $maxRecords = $this->mod()->getVar('items_per_page');

        // Adapted from tiki-g-monitor_activities.php
        include_once(\GALAXIA_LIBRARY . '/processmonitor.php');

        $this->var()->find('filter_process', $data['filter_process'], 'int', '');
        $this->var()->find('filter_activity', $data['filter_activity'], 'str', '');
        $this->var()->find('filter_type', $data['filter_type'], 'str', '');
        $this->var()->find('filter_isInteractive', $data['filter_isInteractive'], 'str', '');
        $this->var()->find('filter_isAutoRouted', $data['filter_isAutoRouted'], 'str', '');

        // Filtering data to be received by request and
        // used to build the where part of a query
        // filter_active, filter_valid, find, sort_mode,
        // filter_process
        $where = '';
        $wheres = [];

        if (!empty($data['filter_isInteractive'])) {
            $wheres[] = "isInteractive='" . $data['filter_isInteractive'] . "'";
        }
        if (!empty($data['filter_isAutoRouted'])) {
            $wheres[] = "isAutoRouted='" . $data['filter_isAutoRouted'] . "'";
        }
        if (!empty($data['filter_process'])) {
            $wheres[] = "pId='" . $data['filter_process'] . "'";
        }
        if (!empty($data['filter_activity'])) {
            $wheres[] = "activityId='" . $data['filter_activity'] . "'";
        }
        if (!empty($data['filter_type'])) {
            $wheres[] = "type='" . $data['filter_type'] . "'";
        }

        $where = implode(' and ', $wheres);

        if (!isset($_REQUEST["sort_mode"])) {
            // FIXME: this string is wrongly converted by convert_sortmode
            //$sort_mode = 'pId_asc, flowNum_asc';
            $sort_mode = 'pId_asc';
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

        $items = $processMonitor->monitor_list_activities($offset - 1, $maxRecords, $sort_mode, $find, $where);
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

        $maxtime = 0;
        foreach ($items['data'] as $info) {
            if (isset($info['duration']) && $maxtime < $info['duration']['max']) {
                $maxtime = $info['duration']['max'];
            }
        }
        if ($maxtime > 0) {
            $scale = 200.0 / $maxtime;
        } else {
            $scale = 1.0;
        }
        foreach ($items['data'] as $index => $info) {
            if (isset($info['duration'])) {
                $items['data'][$index]['duration']['min'] = $userapi->timetodhms(['time' => $info['duration']['min']]);
                $items['data'][$index]['duration']['avg'] = $userapi->timetodhms(['time' => $info['duration']['avg']]);
                $items['data'][$index]['duration']['max'] = $userapi->timetodhms(['time' => $info['duration']['max']]);
                $info['duration']['max'] -= $info['duration']['avg'];
                $info['duration']['avg'] -= $info['duration']['min'];
                $items['data'][$index]['timescale'] = [];
                $items['data'][$index]['timescale']['max'] = intval($scale * $info['duration']['max']);
                $items['data'][$index]['timescale']['avg'] = intval($scale * $info['duration']['avg']);
                $items['data'][$index]['timescale']['min'] = intval($scale * $info['duration']['min']);
            }
        }

        $allprocs = $processMonitor->monitor_list_all_processes('name_asc');
        $data['all_procs'] = [];
        foreach ($allprocs as $row) {
            $data['all_procs'][] = ['id' => $row['pId'], 'name' => $row['name'] . ' ' . $row['version']];
        }

        $pid2name = [];
        foreach ($data['all_procs'] as $info) {
            $pid2name[$info['id']] = $info['name'];
        }
        foreach (array_keys($data['items']) as $index) {
            $pid = $data['items'][$index]['pId'];
            if (isset($pid2name[$pid])) {
                $data['items'][$index]['procname'] = $pid2name[$pid];
            } else {
                $data['items'][$index]['procname'] = '?';
            }
        }

        if (isset($_REQUEST['filter_process']) && $_REQUEST['filter_process']) {
            $where = ' pId=' . $_REQUEST['filter_process'];
        } else {
            $where = '';
        }

        $all_acts = $processMonitor->monitor_list_all_activities('name_asc', $where);
        $data['all_acts'] = &$all_acts;
        $types = $processMonitor->monitor_list_activity_types();
        $data['types'] = &$types;

        $data['stats'] =  $processMonitor->monitor_stats();
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

        $data['mid'] =  'tiki-g-monitor_activities.tpl';

        /*    $data['pager'] = $this->tpl()->getPager($data['offset'],
                                               $items['cant'],
                                               $url,
                                               $maxRecords);*/
        $data['url'] = $this->ctl()->getCurrentURL(['offset' => '%%']);
        $data['maxRecords'] = $maxRecords;
        return $data;
    }
}
