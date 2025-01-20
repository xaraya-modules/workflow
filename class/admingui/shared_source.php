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
use xarSecurity;
use xarTpl;
use xarVar;
use sys;
use BadParameterException;

sys::import('xaraya.modules.method');

/**
 * workflow admin shared_source function
 * @extends MethodClass<AdminGui>
 */
class SharedSourceMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * the shared source administration function
     * @author mikespub
     * @access public
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

        // Adapted from tiki-g-admin_shared_source.php

        include_once(GALAXIA_LIBRARY . '/processmanager.php');

        if (!isset($_REQUEST['pid'])) {
            $data['msg'] =  $this->ml("No process indicated");
            $data['context'] ??= $this->getContext();
            return $this->mod()->template('errors', $data);
        }

        $data['pid'] =  $_REQUEST['pid'];

        if (isset($_REQUEST['code'])) {
            unset($_REQUEST['template']);
            $_REQUEST['save'] = 'y';
        }

        $process = new \Galaxia\Api\Process($_REQUEST['pid']);
        $proc_info = $processManager->get_process($_REQUEST['pid']);
        $proc_info['graph'] = $process->getGraph();
        $data['proc_info'] = &$proc_info;

        $procname = $process->getNormalizedName();

        $data['warn'] =  '';

        if (!isset($_REQUEST['activityId'])) {
            $_REQUEST['activityId'] = 0;
        }

        $data['activityId'] =  $_REQUEST['activityId'];

        if ($_REQUEST['activityId']) {
            $act = \Galaxia\Api\WorkflowActivity::get($_REQUEST['activityId']);

            $actname = $act->getNormalizedName();

            if (isset($_REQUEST['template'])) {
                $data['template'] =  1;

                $source = GALAXIA_PROCESSES . "/$procname/code/templates/$actname" . '.xt';
            } else {
                $data['template'] =  0;

                $source = GALAXIA_PROCESSES . "/$procname/code/activities/$actname" . '.php';
            }

            // Then editing an activity
            $data['act_info'] =  [
                'isInteractive' => $act->isInteractive() ? 1 : 0,
                'type'          => $act->getType(), ];
        } else {
            $data['template'] =  0;
            $data['act_info'] =  ['isInteractive' => 0, 'type' => 'shared'];
            // Then editing shared code
            $source = GALAXIA_PROCESSES . "/$procname/code/shared.php";
        }

        //First of all save
        $this->var()->find('source_data', $source_data, 'str', '');
        if (!empty($source_data)) {
            $source_data = htmlspecialchars_decode($source_data);
            //var_dump($source);$this->exit();
            // security check on paths
            $basedir = GALAXIA_PROCESSES . "/$procname/code/";
            $basepath = realpath($basedir);
            $sourcepath = realpath($_REQUEST['source_name']);
            if (substr($sourcepath, 0, strlen($basepath)) == $basepath) {
                $fp = fopen($_REQUEST['source_name'], "w");
                fwrite($fp, $source_data);
                fclose($fp);
                if ($_REQUEST['activityId']) {
                    $act = \Galaxia\Api\WorkflowActivity::get($_REQUEST['activityId']);
                    $act->compile();
                }
            } else {
                $this->exit('potential hack attack');
            }
        }

        $data['source_name'] =  $source;

        $fp = fopen($source, "r");
        $data['data'] = '';
        while (!feof($fp)) {
            $filestring = fread($fp, 4096);
            $data['data'] .=  $filestring;
        }
        fclose($fp);

        // initialize template
        if (empty($data['data']) && isset($_REQUEST['template']) && !empty($act)) {
            $data['data'] = '<xar:template xmlns:xar="http://xaraya.com/2004/blocklayout">';
            $data['data'] .= "\n" . $act->name;
            $data['data'] .= "\n" . '</xar:template>';
        }

        $valid = $activityManager->validate_process_activities($_REQUEST['pid']);
        $errors = [];

        if (!$valid) {
            $errors = $activityManager->get_error();

            $proc_info['isValid'] = 0;
        } else {
            $proc_info['isValid'] = 1;
        }

        $data['errors'] =  $errors;

        $activities = $activityManager->list_activities($_REQUEST['pid'], 0, -1, 'name_asc', '');
        $data['items'] = $activities['data'];

        $data['mid'] =  'tiki-g-admin_shared_source.tpl';

        return $data;
    }
}
