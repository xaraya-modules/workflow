<?php

/**
 * @package modules\workflow
 * @category Xaraya Web Applications Framework
 * @version 2.6.2
 * @copyright see the html/credits.html file in this release
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link https://github.com/mikespub/xaraya-modules
**/

namespace Xaraya\Modules\Workflow\SchedulerApi;

use Xaraya\Modules\MethodClass;
use Xaraya\Modules\Workflow\SchedulerApi;
use xarMLS;
use xarMod;
use xarModVars;
use xarVar;
use sys;

sys::import('xaraya.modules.method');

/**
 * workflow schedulerapi activities function
 * @extends MethodClass<SchedulerApi>
 */
class  ActivitiesMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * run all scheduled workflow activities (executed by the scheduler module)
     * @author mikespub
     * @access public
     * @see SchedulerApi::activities()
     */
    public function __invoke(array $args = [])
    {
        // We need to keep track of our own set of jobs here, because the scheduler won't know what
        // workflow activities to run when. Other modules will typically have 1 job that corresponds
        // to 1 API function, so they won't need this...
    
        $log = $this->ml('Starting scheduled workflow activities') . "\n";
        $serialjobs = $this->mod()->getVar('jobs');
        if (!empty($serialjobs)) {
            $jobs = unserialize($serialjobs);
        } else {
            $jobs = [];
        }
        $hasrun = [];
        $now = time() + 60; // add some margin here
        foreach ($jobs as $id => $job) {
            $lastrun = $job['lastrun'];
            if (!empty($lastrun)) {
                if (!preg_match('/(\d+)(\w)/', $job['interval'], $matches)) {
                    continue;
                }
                $count = intval($matches[1]);
                $interval = $matches[2];
                $skip = 0;
                switch ($interval) {
                    case 'h':
                        if ($now - $lastrun < $count * 60 * 60) {
                            $skip = 1;
                        }
                        break;
                    case 'd':
                        if ($now - $lastrun < $count * 24 * 60 * 60) {
                            $skip = 1;
                        }
                        break;
                    case 'w':
                        if ($now - $lastrun < $count * 7 * 24 * 60 * 60) {
                            $skip = 1;
                        }
                        break;
                    case 'm': // work with day of the month here
                        $new = getdate($now);
                        $old = getdate($lastrun);
                        $new['mon'] += 12 * ($new['year'] - $old['year']);
                        if ($new['mon'] < $old['mon'] + $count) {
                            $skip = 1;
                        } elseif ($new['mon'] == $old['mon'] + $count && $new['mday'] < $old['mday']) {
                            $skip = 1;
                        }
                        break;
                }
                if ($skip) {
                    continue;
                }
            }
            $log .= $this->ml('Workflow activity #(1)', $job['activity']) . ' ';
            if (!$this->mod()->apiMethod(
                'workflow',
                'user',
                'run_activity',
                ['activityId' => $job['activity']]
            )) {
                $jobs[$id]['result'] = $this->ml('failed');
                $log .= $this->ml('failed');
            } else {
                $jobs[$id]['result'] = $this->ml('OK');
                $log .= $this->ml('succeeded');
            }
            $jobs[$id]['lastrun'] = $now - 60; // remove the margin here
            $hasrun[] = $id;
            $log .= "\n";
        }
        $log .= $this->ml('Finished scheduled workflow activities');
    
        // we didn't run anything, so return now
        if (count($hasrun) == 0) {
            return $log;
        }
    
        // Trick : make sure we're dealing with up-to-date information here,
        //         because running all those jobs may have taken a while...
        $this->var()->delCached('Mod.Variables.workflow', 'jobs');
    
        // get the current list of jobs
        $serialjobs = $this->mod()->getVar('jobs');
        if (!empty($serialjobs)) {
            $newjobs = unserialize($serialjobs);
        } else {
            $newjobs = [];
        }
        // set the job information
        foreach ($hasrun as $id) {
            if (!isset($newjobs[$id])) {
                continue;
            }
            // make sure we're dealing with the same job here :)
            if ($newjobs[$id]['activity'] == $jobs[$id]['activity'] &&
                $newjobs[$id]['lastrun'] < $jobs[$id]['lastrun']) {
                $newjobs[$id]['result'] = $jobs[$id]['result'];
                $newjobs[$id]['lastrun'] = $jobs[$id]['lastrun'];
            }
        }
        // update the new jobs
        $serialjobs = serialize($newjobs);
        $this->mod()->setVar('jobs', $serialjobs);
    
        return $log;
    }
}

