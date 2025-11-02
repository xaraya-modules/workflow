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
 * workflow admin graph function
 * @extends MethodClass<AdminGui>
 */
class GraphMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * the graph administration function
     * @author mikespub
     * @access public
     * @see AdminGui::graph()
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
        $maxRecords = $this->mod()->getVar('items_per_page');
        // Adapted from tiki-g-admin_processes.php

        include_once(\GALAXIA_LIBRARY . '/processmanager.php');

        // Check if we are editing an existing process
        // if so retrieve the process info and assign it.
        if (!isset($_REQUEST['pid'])) {
            $_REQUEST['pid'] = 0;
        }

        if ($_REQUEST["pid"]) {
            $this->log()->debug("WORKFLOW: Getting process");
            $process = new \Galaxia\Api\Process($_REQUEST['pid']);
            $procNName = $process->getNormalizedName();

            $info = $processManager->get_process($_REQUEST["pid"]);

            $info['graph'] = \GALAXIA_PROCESSES . "/" . $procNName . "/graph/" . $procNName . ".png";
            $mapfile = \GALAXIA_PROCESSES . "/" . $procNName . "/graph/" . $procNName . ".map";

            if (!file_exists($process->getGraph()) or !file_exists($mapfile)) {
                // Try to build it
                $this->log()->debug("WF: need to build graph files");
                $activityManager->build_process_graph($_REQUEST['pid']);
            }

            if (file_exists($process->getGraph()) && file_exists($mapfile)) {
                $this->log()->debug("WF: graph files exist");
                $map = join('', file($mapfile));

                $url = $this->mod()->getURL(
                    'admin',
                    'activities',
                    ['pid' => $info['pId']]
                );
                $map = preg_replace('/href=".*?activityId/', 'href="' . $url . '&amp;activityId', $map);
                // Darn graphviz does not close the area tags
                $map = preg_replace('#<area (.*[^/])>#', '<area $1/>', $map);
                $info['map'] = $map;
            } else {
                $info['graph'] = '';
            }
        } else {
            $info = [
                'name' => '',
                'description' => '',
                'version' => '1.0',
                'isActive' => 0,
                'pId' => 0,
            ];
        }

        $tplData['proc_info'] = $info;
        $tplData['pid'] =  $_REQUEST['pid'];
        $tplData['info'] =  $info;

        $where = '';
        $wheres = [];

        if (isset($_REQUEST['filter'])) {
            if ($_REQUEST['filter_name']) {
                $wheres[] = " name='" . $_REQUEST['filter_name'] . "'";
            }

            if ($_REQUEST['filter_active']) {
                $wheres[] = " isActive='" . $_REQUEST['filter_active'] . "'";
            }

            $where = implode('and', $wheres);
        }

        if (isset($_REQUEST['where'])) {
            $where = $_REQUEST['where'];
        }

        if (!isset($_REQUEST["sort_mode"])) {
            $sort_mode = 'lastModif_desc';
        } else {
            $sort_mode = $_REQUEST["sort_mode"];
        }

        if (!isset($_REQUEST["offset"])) {
            $offset = 1;
        } else {
            $offset = $_REQUEST["offset"];
        }

        $tplData['offset'] = $offset;

        if (isset($_REQUEST["find"])) {
            $find = $_REQUEST["find"];
        } else {
            $find = '';
        }

        $tplData['find'] =  $find;
        $tplData['where'] =  $where;
        $tplData['sort_mode'] = $sort_mode;

        $items = $processManager->list_processes($offset - 1, $maxRecords, $sort_mode, $find, $where);
        $tplData['cant'] =  $items['cant'];

        $cant_pages = ceil($items["cant"] / $maxRecords);
        $tplData['cant_pages'] =  $cant_pages;
        $tplData['actual_page'] =  1 + (($offset - 1) / $maxRecords);

        if ($items["cant"] >= ($offset + $maxRecords)) {
            $tplData['next_offset'] =  $offset + $maxRecords;
        } else {
            $tplData['next_offset'] =  -1;
        }

        if ($offset > 1) {
            $tplData['prev_offset'] =  $offset - $maxRecords;
        } else {
            $tplData['prev_offset'] =  -1;
        }

        $tplData['items'] =  $items["data"];

        if ($_REQUEST['pid']) {
            $valid = $activityManager->validate_process_activities($_REQUEST['pid']);

            $errors = [];

            if (!$valid) {
                $process->deactivate();

                $errors = $activityManager->get_error();
            }

            $tplData['errors'] =  $errors;
        }

        $sameurl_elements = [
            'offset',
            'sort_mode',
            'where',
            'find',
            'filter_name',
            'filter_active',
        ];

        $all_procs = $items = $processManager->list_processes(0, -1, 'name_desc', '', '');
        $tplData['all_procs'] =  $all_procs['data'];

        $tplData['mid'] =  'tiki-g-admin_processes.tpl';

        /*    $tplData['pager'] = $this->tpl()->getPager($tplData['offset'],
                                               $items['cant'],
                                               $url,
                                               $maxRecords);*/
        $tplData['url'] = $this->ctl()->getCurrentURL(['offset' => '%%']);
        $tplData['maxRecords'] = $maxRecords;
        return $tplData;
    }
}
