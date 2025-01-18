<?php
/**
 * Workflow Module
 *
 * @package modules
 * @copyright (C) copyright-placeholder
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link http://www.xaraya.com
 *
 * @subpackage Workflow Module
 * @link http://xaraya.com/index.php/release/188.html
 * @author Workflow Module Development Team
 */
use Xaraya\Modules\Workflow\WorkflowQueue;

/**
 * run all scheduled workflow transitions (executed by the scheduler module)
 *
 * This can be based on queued transition events from workflow_queue (if used) and/or
 * active workflow subjects based on their place or last update from workflow_tracker
 *
 * For example, with the 'article' workflow the spell checker can be:
 * 1. automatically called at the end of the 'request_review' transition (in-line), or
 * 2. queued as event for later processing as called here by the scheduler module, or
 * 3. waiting for this to check all articles active in the workflow tracker, to see
 *    if they are in place 'wait_for_spellchecker' and then process them
 *
 * @uses \sys::autoload()
 */
function workflow_schedulerapi_transitions(array $args = [], $context = null)
{
    sys::autoload();
    $log = xarMLS::translate('Starting scheduled workflow transitions') . "\n";
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
            $result = xarMod::apiFunc('workflow', 'user', 'run_transition', $params, $context);
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
