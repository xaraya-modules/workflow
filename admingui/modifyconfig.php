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
use Xaraya\Modules\Workflow\WorkflowConfig;
use Xaraya\Modules\MethodClass;
use Exception;

/**
 * workflow admin modifyconfig function
 * @extends MethodClass<AdminGui>
 */
class ModifyconfigMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * Update the configuration parameters of the module based on data from the modification form
     * @author mikespub
     * @access public
     * @return array|void true on success or void on failure
     * @see AdminGui::modifyconfig()
     */
    public function __invoke(array $args = [])
    {
        // Security Check
        if (!$this->sec()->checkAccess('AdminWorkflow')) {
            return;
        }

        $data = [];
        $data['settings'] = [];

        $data['module_settings'] = $this->mod()->apiFunc('base', 'admin', 'getmodulesettings', ['module' => 'workflow']);
        $data['module_settings']->getItem();

        $create = $this->mod()->getVar('default.create');
        $update = $this->mod()->getVar('default.update');
        $delete = $this->mod()->getVar('default.delete');
        $data['settings']['default'] = ['label' => $this->ml('Default configuration'),
            'create' => $create,
            'update' => $update,
            'delete' => $delete, ];

        $hookedmodules = $this->mod()->apiFunc(
            'modules',
            'admin',
            'gethookedmodules',
            ['hookModName' => 'workflow']
        );
        if (isset($hookedmodules) && is_array($hookedmodules)) {
            foreach ($hookedmodules as $modname => $value) {
                // we have hooks for individual item types here
                if (!isset($value[0])) {
                    // Get the list of all item types for this module (if any)
                    try {
                        $mytypes = $this->mod()->apiFunc($modname, 'user', 'getitemtypes');
                    } catch (Exception $e) {
                        $mytypes = [];
                    }
                    foreach ($value as $itemtype => $val) {
                        $create = $this->mod()->getVar("$modname.$itemtype.create");
                        if (empty($create)) {
                            $create = '';
                        }
                        $update = $this->mod()->getVar("$modname.$itemtype.update");
                        if (empty($update)) {
                            $update = '';
                        }
                        $delete = $this->mod()->getVar("$modname.$itemtype.delete");
                        if (empty($delete)) {
                            $delete = '';
                        }
                        if (isset($mytypes[$itemtype])) {
                            $type = $mytypes[$itemtype]['label'];
                            $link = $mytypes[$itemtype]['url'];
                        } else {
                            $type = $this->ml('type #(1)', $itemtype);
                            $link = $this->ctl()->getModuleURL($modname, 'user', 'view', ['itemtype' => $itemtype]);
                        }
                        $data['settings']["$modname.$itemtype"] = ['label' => $this->ml('Configuration for #(1) module - <a href="#(2)">#(3)</a>', $modname, $link, $type),
                            'create' => $create,
                            'update' => $update,
                            'delete' => $delete, ];
                    }
                } else {
                    $create = $this->mod()->getVar("$modname.create");
                    if (empty($create)) {
                        $create = '';
                    }
                    $update = $this->mod()->getVar("$modname.update");
                    if (empty($update)) {
                        $update = '';
                    }
                    $delete = $this->mod()->getVar("$modname.delete");
                    if (empty($delete)) {
                        $delete = '';
                    }
                    $link = $this->ctl()->getModuleURL($modname, 'user', 'main');
                    $data['settings'][$modname] = ['label' => $this->ml('Configuration for <a href="#(1)">#(2)</a> module', $link, $modname),
                        'create' => $create,
                        'update' => $update,
                        'delete' => $delete, ];
                }
            }
        }

        // Common setup for Galaxia environment
        require_once dirname(__DIR__) . '/lib/galaxia/config.php';
        include_once(\GALAXIA_LIBRARY . '/processmonitor.php');

        // get all start activities that are not interactive
        $activities = $processMonitor->monitor_list_activities(0, -1, 'pId_asc', '', "type='start' and isInteractive=0");

        // get the name of all processes
        $all_procs = $processMonitor->monitor_list_all_processes('pId_asc', "isActive=1");
        $pid2name = [];
        foreach ($all_procs as $info) {
            $pid2name[$info['pId']] = $info['name'] . ' ' . $info['version'];
        }

        // build a list of activity ids and names
        $data['activities'] = [];
        $data['activities'][0] = '';
        foreach ($activities['data'] as $info) {
            if (isset($pid2name[$info['pId']])) {
                $data['activities'][$info['activityId']] = $pid2name[$info['pId']] . ' - ' . $info['name'];
            }
        }

        // get all stand-alone activities that are not interactive
        $activities = $processMonitor->monitor_list_activities(0, -1, 'pId_asc', '', "type='standalone' and isInteractive=0");

        // build a list of activity ids and names
        $data['standalone'] = [];
        foreach ($activities['data'] as $info) {
            if (isset($pid2name[$info['pId']])) {
                $data['standalone'][$info['activityId']] = $pid2name[$info['pId']] . ' - ' . $info['name'];
            }
        }

        // for Symfony Workflows build a list of transitions from initial marking
        $config = WorkflowConfig::loadConfig();
        //$data['transitions'] = [];
        foreach ($config as $workflowName => $info) {
            $start = $info['initial_marking'];
            $start = !is_array($start) ?: $start[0];
            $label = ($info['label'] ?? $workflowName) . " : $start";
            $label = WorkflowConfig::formatName($label);
            foreach ($info['transitions'] as $transitionName => $fromto) {
                $name = WorkflowConfig::formatName($transitionName);
                if (is_array($fromto['from']) && in_array($start, $fromto['from'])) {
                    //$data['transitions'][$workflowName] ??= [];
                    //$data['transitions'][$workflowName][$transitionName] = "$label - $name";
                    $data['activities']["$workflowName/$transitionName"] = "$label - $name";
                } elseif (!is_array($fromto['from']) && $start == $fromto['from']) {
                    //$data['transitions'][$workflowName] ??= [];
                    //$data['transitions'][$workflowName][$transitionName] = "$label - $name";
                    $data['activities']["$workflowName/$transitionName"] = "$label - $name";
                }
            }
        }

        // We need to keep track of our own set of jobs here, because the scheduler won't know what
        // workflow activities to run when. Other modules will typically have 1 job that corresponds
        // to 1 API function, so they won't need this...

        $serialjobs = $this->mod()->getVar('jobs');
        if (!empty($serialjobs)) {
            $data['jobs'] = unserialize($serialjobs);
        } else {
            $data['jobs'] = [];
        }
        $data['jobs'][] = ['activity' => '',
            'interval' => '',
            'lastrun' => '',
            'result' => '', ];

        if ($this->mod()->isAvailable('scheduler')) {
            $data['intervals'] = $this->mod()->apiFunc('scheduler', 'user', 'intervals');
            // see if we have a scheduler job running to execute workflow activities
            $job = $this->mod()->apiFunc(
                'scheduler',
                'user',
                'get',
                ['module' => 'workflow',
                    'type' => 'scheduler',
                    'func' => 'activities', ]
            );
            if (empty($job) || empty($job['interval'])) {
                $data['interval'] = '';
            } else {
                $data['interval'] = $job['interval'];
            }
        } else {
            $data['intervals'] = [];
            $data['interval'] = '';
        }

        $data['authid'] = $this->sec()->genAuthKey();
        return $data;
    }
}
