<?php
/**
 * Dummy spell checker for Symfony Workflow events
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

namespace Xaraya\Modules\Publications;

use Symfony\Component\Workflow\WorkflowInterface;
use Xaraya\Modules\Workflow\WorkflowHandlers;
use Xaraya\Modules\Workflow\WorkflowSubject;
use xarLog;
use sys;

sys::import('modules.workflow.class.subject');
sys::import('modules.workflow.class.handlers');
sys::import('xaraya.facades.logger');
use Xaraya\Facades\xarLog3;

/**
 * Dummy spell checker to show-case automatically running something on
 * the subject once a transition is completed (or we entered a place),
 * and possibly apply another transition to move the workflow forward
 * in case of success and/or failure
 *
 * This can also be configured to queue the transition event for later
 * processing e.g. by the scheduler module or manual queue processing
 */
class SpellCheckerDummy
{
    /**
     * Check if text contains 'OK'
     * @todo use real spell checker ;-)
     * @return bool
     */
    public static function checkSpelling(string $text)
    {
        if (str_contains($text, 'OK')) {
            return true;
        }
        return false;
    }

    /**
     * Dummy spell checker service - @todo use real spell checker
     * @param list<string> $fields object properties to spell check
     * @param string $success transition name in case of success (if any)
     * @param string $failure transition name in case of failure (if any)
     * @return mixed
     */
    public static function runSpellChecker(WorkflowInterface $workflow, WorkflowSubject $subject, array $fields, string $success, string $failure)
    {
        $objectRef = $subject->getObject();
        $values = $objectRef->getFieldValues($fields, 1);
        $context = $subject->getContext() ?? [];
        if ($context instanceof \ArrayObject) {
            $context = $context->getArrayCopy();
        }
        $context['spellchecker'] ??= [];
        $result = $success;
        foreach ($fields as $field) {
            if (!empty($values[$field])) {
                if (static::checkSpelling($values[$field])) {
                    $context['spellchecker'][$field] = 'Field ' . $field . ' is OK';
                    continue;
                }
                $result = $failure;
                $context['spellchecker'][$field] = 'Field ' . $field . ' is not OK';
            }
        }
        if (!empty($result)) {
            return $workflow->apply($subject, $result, $context);
        }
        return null;
    }

    /**
     * Callback function to automatically start the spell checker once the request_review transition
     * is completed, or to queue the transition event for later processing
     * Note: we could also trigger this via the workflow.article.entered.wait_for_spellchecker event (not used)
     * @param list<string> $fields object properties to spell check
     * @param string $success transition name in case of success (if any)
     * @param string $failure transition name in case of failure (if any)
     * @param bool $queued queue this transition event for later processing or not (default=false)
     */
    public static function startSpellCheckerHandler(array $fields, string $success, string $failure, bool $queued = false)
    {
        // Note: we could do something with $dispatcher too
        $handler = function ($event, $eventName, $dispatcher) use ($fields, $success, $failure, $queued) {
            $subject = $event->getSubject();
            $context = $event->getContext();
            // @checkme let the event handler called for this transition know that we have been scheduled (or not)
            if ($queued && empty($context['scheduled'])) {
                // queue spell checker event
                $queueId = WorkflowHandlers::doQueueEvent($event, $eventName, $dispatcher);
                $message = "The spell checker is queued with id ($queueId) for subject " . $subject->getId();
                xarLog3::info("Event $eventName queued: $message");
                return $queueId;
            }
            $workflow = $event->getWorkflow();
            $marking = static::runSpellChecker($workflow, $subject, $fields, $success, $failure);
            if (!empty($marking)) {
                $places = implode(', ', array_keys($marking->getPlaces()));
                $message = "The spell checker resulted in transition of subject " . $subject->getId() . " to " . $places;
            } else {
                $message = "The spell checker resulted in NO transition for subject " . $subject->getId();
            }
            xarLog3:info("Event $eventName handled: $message");
            return true;
        };
        return $handler;
    }
}
