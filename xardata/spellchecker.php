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
use Xaraya\Modules\Workflow\WorkflowSubject;
use xarLog;
use sys;

sys::import('modules.workflow.class.subject');

/**
 * Dummy spell checker to show-case automatically running something on
 * the subject once a transition is completed (or we entered a place),
 * and possibly apply another transition to move the workflow forward
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
     * @param string $success transition name in case of success
     * @param string $failure transition name in case of failure
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
     * is completed
     * Note: we could also trigger this via the workflow.article.entered.wait_for_spellchecker event
     */
    public static function startSpellCheckerHandler(array $fields, string $success, string $failure, bool $queued = false)
    {
        // Note: we could do something with $dispatcher too
        $handler = function ($event, $eventName, $dispatcher) use ($fields, $success, $failure, $queued) {
            $subject = $event->getSubject();
            if ($queued) {
                // @todo queue spell checker event
                $message = "The spell checker is queued for subject " . $subject->getId();
                xarLog::message("Event $eventName queued: $message", xarLog::LEVEL_INFO);
                return;
            }
            $workflow = $event->getWorkflow();
            $marking = static::runSpellChecker($workflow, $subject, $fields, $success, $failure);
            if (!empty($marking)) {
                $places = implode(', ', array_keys($marking->getPlaces()));
                $message = "The spell checker resulted in transition of subject " . $subject->getId() . " to " . $places;
            } else {
                $message = "The spell checker resulted in NO transition for subject " . $subject->getId();
            }
            xarLog::message("Event $eventName handled: $message", xarLog::LEVEL_INFO);
        };
        return $handler;
    }
}
