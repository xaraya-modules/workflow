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
use Xaraya\Modules\Workflow\WorkflowQueue;
use Exception;
use sys;

/**
 * workflow schedulerapi transitions function
 * @extends MethodClass<SchedulerApi>
 */
class TransitionsMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * run all scheduled workflow transitions (executed by the scheduler module)
     * This can be based on queued transition events from workflow_queue (if used) and/or
     * active workflow subjects based on their place or last update from workflow_tracker
     *
     * For example, with the 'article' workflow the spell checker can be:
     * 1. automatically called at the end of the 'request_review' transition (in-line), or
     * 2. queued as event for later processing as called here by the scheduler module, or
     * 3. waiting for this to check all articles active in the workflow tracker, to see
     *    if they are in place 'wait_for_spellchecker' and then process them
     * @uses \sys::autoload()
     * @see SchedulerApi::transitions()
     */
    public function __invoke(array $args = [])
    {
        sys::autoload();
        $log = $this->ml('Starting scheduled workflow transitions') . "\n";
        // let the event handler called for this transition know that we have been scheduled
        if (empty($args['scheduled'])) {
            $args['scheduled'] = 'scheduler';
        }

        // get queued events for all workflows, subjects and users
        $items = WorkflowQueue::getItems();
        foreach ($items as $item) {
            $params = [
                'workflow' => $item['workflow'],
                'subjectId' => $item['subject'],
                'transition' => $item['transition'],
            ];
            // @checkme let the event handler called for this transition know that we have been scheduled
            $params['scheduled'] = $args['scheduled'];
            try {
                $result = $this->mod()->apiMethod('workflow', 'userapi', 'run_transition', $params);
                $log .= "Scheduled transition " . $item['transition'] . " in workflow " . $item['workflow'] . " for subject " . $item['subject'] . " succeeded";
                $id = WorkflowQueue::delete($item['id']);
            } catch (Exception $e) {
                $log .= "Scheduled transition " . $item['transition'] . " in workflow " . $item['workflow'] . " for subject " . $item['subject'] . " failed:\n";
                $log .= $e->getMessage();
            }
            $log .= "\n";
        }
        $log .= "Done\n";

        // @todo and/or check workflow tracker for specific conditions

        return $log;
    }
}
