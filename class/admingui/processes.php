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
use xarLog;
use xarSecurity;
use xarModVars;
use xarTpl;
use xarVar;
use xarTplPager;
use xarServer;
use sys;
use BadParameterException;

sys::import('xaraya.modules.method');

/**
 * workflow admin processes function
 * @extends MethodClass<AdminGui>
 */
class ProcessesMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * the processes administration function
     * @author mikespub
     * @access public
     */
    public function __invoke(array $args = [])
    {
        xarLog::message('WF: workflow_admin_processes ');
        // Security Check
        if (!$this->checkAccess('AdminWorkflow')) {
            return;
        }

        // Common setup for Galaxia environment
        sys::import('modules.workflow.lib.galaxia.config');
        $data = [];
        $maxRecords = $this->getModVar('items_per_page');

        // Adapted from tiki-g-admin_processes.php
        include_once(GALAXIA_LIBRARY . '/processmanager.php');

        // Initialize
        $data['proc_info'] = [
            'name'          => '',
            'description'   => '',
            'version'       => '1.0',
            'isActive'      => 0,
            'pId'           => 0, ];

        // Check if we are editing an existing process
        // if so retrieve the process info and assign it.
        if (!isset($_REQUEST['pid'])) {
            $_REQUEST['pid'] = 0;
        }
        if ($_REQUEST['pid']) {
            $process = new \Galaxia\Api\Process($_REQUEST['pid']);
            $data['proc_info'] = $processManager->get_process($_REQUEST["pid"]);
            $data['proc_info']['graph'] = $process->getGraph();
        }
        $data['pid'] =  $_REQUEST['pid'];

        //Check here for an uploaded process
        xarLog::message('WF: checking for uploaded process');
        if (isset($_FILES['userfile1']) && is_uploaded_file($_FILES['userfile1']['tmp_name'])) {
            xarLog::message('WF: Found upload file');
            // move the uploaded file to some temporary wf* file in cache/templates
            $tmpdir = sys::varpath() . '/cache/templates';
            $tmpfile = tempnam($tmpdir, 'wf');
            if (move_uploaded_file($_FILES['userfile1']['tmp_name'], $tmpfile) && file_exists($tmpfile)) {
                xarLog::message('WF: Temporary upload file found, reading it in.');
                $fp = fopen($tmpfile, "rb");

                $xml = '';
                $fhash = '';
                // Read it in
                while (!feof($fp)) {
                    $xml .= fread($fp, 8192 * 16);
                }

                fclose($fp);
                $size = $_FILES['userfile1']['size'];
                $name = $_FILES['userfile1']['name'];
                $type = $_FILES['userfile1']['type'];

                $process_data = $processManager->unserialize_process($xml);

                if (\Galaxia\Api\Process::exists($process_data['name'], $process_data['version'])) {
                    $data['msg'] =  $this->translate("The process name already exists");
                    $data['context'] ??= $this->getContext();
                    return xarTpl::module('workflow', 'admin', 'errors', $data);
                } else {
                    $_REQUEST['pid'] = $processManager->import_process($process_data);
                }
                unlink($tmpfile);
            }
        }
        xarLog::message('WF: done with the uploading');

        if (isset($_REQUEST["delete"])) {
            foreach (array_keys($_REQUEST["process"]) as $item) {
                $processManager->remove_process($item);
            }
        }

        // New minor version of the process
        if (isset($_REQUEST['newminor'])) {
            $processManager->new_process_version($_REQUEST['newminor']);
        }

        // New major version of the process
        if (isset($_REQUEST['newmajor'])) {
            $processManager->new_process_version($_REQUEST['newmajor'], false);
        }

        // Update or create action
        if (isset($_REQUEST['save'])) {
            $vars = ['name' => $_REQUEST['name'],
                'description' => $_REQUEST['description'],
                'version' => $_REQUEST['version'],
            ];

            // If process is known and we're not updating, error out.
            if (\Galaxia\Api\Process::Exists($_REQUEST['name'], $_REQUEST['version']) && $_REQUEST['pid'] == 0) {
                $data['msg'] =  $this->translate("Process already exists");
                $data['context'] ??= $this->getContext();
                return xarTpl::module('workflow', 'admin', 'errors', $data);
            }

            $this->fetch('isSingleton', 'int', $vars['isSingleton'], 0, xarVar::NOT_REQUIRED);
            $this->fetch('isActive', 'int', $vars['isActive'], 0, xarVar::NOT_REQUIRED);

            // Replace the info on the process with the new values (or create them)
            $pid = $processManager->replace_process($_REQUEST['pid'], $vars);
            $process = new \Galaxia\Api\Process($pid);
            // Validate the process and deactivate it if it turns out to be invalid.
            $valid = $activityManager->validate_process_activities($pid);
            if (!$valid) {
                $process->deactivate();
            }

            // Reget the process info for the UI
            $process = new \Galaxia\Api\Process($pid);
            $data['proc_info'] = $processManager->get_process($pid);
            $data['proc_info']['graph'] = $process->getGraph();
        }

        // Filtering by name, status or direct
        $data['where'] = '';
        $wheres = [];
        if (isset($_REQUEST['filter'])) {
            if ($_REQUEST['filter_name']) {
                $wheres[] = " name='" . $_REQUEST['filter_name'] . "'";
            }
            if ($_REQUEST['filter_active']) {
                $wheres[] = " isActive='" . $_REQUEST['filter_active'] . "'";
            }
            $data['where'] = implode('and', $wheres);
        }
        if (isset($_REQUEST['where'])) {
            $data['where'] = $_REQUEST['where'];
        }

        // Specific sorting specified?
        $data['sort_mode'] = $_REQUEST["sort_mode"] ?? 'lastModif_desc';
        // Offset into the processlist
        $data['offset'] = $_REQUEST["offset"] ?? 1;
        // Specific find text
        $data['find'] = $_REQUEST["find"] ?? '';

        // Validate the process
        if ($_REQUEST['pid']) {
            $valid = $activityManager->validate_process_activities($_REQUEST['pid']);
            $data['errors'] = [];
            if (!$valid) {
                $process = new \Galaxia\Api\Process($_REQUEST['pid']);
                $process->deactivate();
                $data['errors'] = $activityManager->get_error();
            }
        }

        $items = $processManager->list_processes($data['offset'] - 1, $maxRecords, $data['sort_mode'], $data['find'], $data['where']);
        $data['cant'] =  $items['cant'];

        $data['cant_pages'] =  ceil($items["cant"] / $maxRecords);
        $data['actual_page'] =  1 + (($data['offset'] - 1) / $maxRecords);

        $data['next_offset'] =  -1;
        if ($items["cant"] >= ($data['offset'] + $maxRecords)) {
            $data['next_offset'] =  $data['offset'] + $maxRecords;
        }

        $data['prev_offset'] =  -1;
        if ($data['offset'] > 1) {
            $data['prev_offset'] =  $data['offset'] - $maxRecords;
        }
        $data['items'] =  $items["data"];

        $data['all_procs'] =  $items['data'];

        //    $data['pager'] = xarTplPager::getPager($data['offset'], $items['cant'], $url, $maxRecords);
        $data['url'] = xarServer::getCurrentURL(['offset' => '%%']);
        $data['maxRecords'] = $maxRecords;

        return $data;
    }
}
