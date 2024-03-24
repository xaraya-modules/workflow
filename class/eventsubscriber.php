<?php
/**
 * Workflow Module Event Subscriber for Symfony Workflow events
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

namespace Xaraya\Modules\Workflow;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\Event;
use Symfony\Component\Workflow\Event\GuardEvent;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use xarLog;
use Exception;

/**
 * @uses \sys::autoload()
 */
class WorkflowEventSubscriber extends WorkflowBase implements EventSubscriberInterface
{
    private static $eventNamePrefix = 'workflow.';
    private static $subscribedEvents = [];
    private static $callbackFunctions = [];
    private static $eventTypeMethods = [
        'guard' => 'onGuardEvent',
        'leave' => 'onLeaveEvent',
        'transition' => 'onTransitionEvent',
        'enter' => 'onEnterEvent',
        'entered' => 'onEnteredEvent',
        'completed' => 'onCompletedEvent',
        'announce' => 'onAnnounceEvent',
    ];

    public function callBack(Event|GuardEvent $event, string $eventName, EventDispatcherInterface $dispatcher)
    {
        if (empty(self::$callbackFunctions[$eventName])) {
            return;
        }
        // @todo do we want to keep track of callback results here?
        foreach (self::$callbackFunctions[$eventName] as $callbackFunc) {
            try {
                $callbackFunc($event, $eventName, $dispatcher);
            } catch (Exception $e) {
                xarLog::message("Error in callback for $eventName: " . $e->getMessage(), xarLog::LEVEL_INFO);
            }
        }
    }

    public function logEvent(Event|GuardEvent $event, string $eventName, EventDispatcherInterface $dispatcher)
    {
        // @todo tie in with Xaraya event/hook system for some events
        $marking = $event->getMarking();
        $subject = $event->getSubject();
        $transition = $event->getTransition();
        //$workflowName = $event->getWorkflowName();
        //$metadata = $event->getMetadata();
        $message = sprintf(
            '%s: Subject (id: "%s") had event "%s" for transition "%s" from "%s" to "%s" with %s callbacks',
            'Workflow',
            isset($subject) ? $subject->getId() : '',
            $eventName,
            isset($transition) ? $transition->getName() : '',
            isset($marking) ? implode(', ', array_keys($marking->getPlaces())) : '',
            isset($transition) ? implode(', ', $transition->getTos()) : '',
            count(self::$callbackFunctions[$eventName] ?? [])
        );
        xarLog::message($message, xarLog::LEVEL_INFO);
    }

    public function onGuardEvent(GuardEvent $event, string $eventName, EventDispatcherInterface $dispatcher)
    {
        //$subject = $event->getSubject();
        $this->logEvent($event, $eventName, $dispatcher);
        $this->callBack($event, $eventName, $dispatcher);
        // this would be where we check the actual status of the subject, rather than the places
    }

    public function onLeaveEvent(Event $event, string $eventName, EventDispatcherInterface $dispatcher)
    {
        //$subject = $event->getSubject();
        $this->logEvent($event, $eventName, $dispatcher);
        $this->callBack($event, $eventName, $dispatcher);
    }

    public function onTransitionEvent(Event $event, string $eventName, EventDispatcherInterface $dispatcher)
    {
        //$subject = $event->getSubject();
        $this->logEvent($event, $eventName, $dispatcher);
        $this->callBack($event, $eventName, $dispatcher);
    }

    public function onEnterEvent(Event $event, string $eventName, EventDispatcherInterface $dispatcher)
    {
        //$subject = $event->getSubject();
        $this->logEvent($event, $eventName, $dispatcher);
        $this->callBack($event, $eventName, $dispatcher);
    }

    public function onEnteredEvent(Event $event, string $eventName, EventDispatcherInterface $dispatcher)
    {
        //$subject = $event->getSubject();
        $this->logEvent($event, $eventName, $dispatcher);
        $this->callBack($event, $eventName, $dispatcher);
    }

    public function onCompletedEvent(Event $event, string $eventName, EventDispatcherInterface $dispatcher)
    {
        //$subject = $event->getSubject();
        $this->logEvent($event, $eventName, $dispatcher);
        $this->callBack($event, $eventName, $dispatcher);
        // this is where we add the successful transition to a new marking to the tracker
        // this would be where we update the actual status of the subject, rather than the places
    }

    public function onAnnounceEvent(Event $event, string $eventName, EventDispatcherInterface $dispatcher)
    {
        //$subject = $event->getSubject();
        $this->logEvent($event, $eventName, $dispatcher);
        $this->callBack($event, $eventName, $dispatcher);
    }

    public static function getEventName(string $eventType, string $workflowName = '', string $specificName = '')
    {
        //workflow.guard
        if (empty($workflowName)) {
            $eventName = self::$eventNamePrefix . $eventType;
            //workflow.[workflow name].guard
        } else {
            $eventName = self::$eventNamePrefix . $workflowName . '.' . $eventType;
            //workflow.[workflow name].guard.[transition name]
            if (!empty($specificName)) {
                $eventName .= '.' . $specificName;
            }
        }
        return $eventName;
    }

    public static function addSubscribedEvent(string $eventType, string $workflowName = '', string $specificName = '', ?callable $callbackFunc = null)
    {
        $eventName = static::getEventName($eventType, $workflowName, $specificName);
        self::$subscribedEvents[$eventName] = [self::$eventTypeMethods[$eventType]];
        if (!empty($callbackFunc)) {
            static::addCallbackFunction($eventName, $callbackFunc);
        }
        return $eventName;
    }

    public static function addCallbackFunction(string $eventName, callable $callbackFunc)
    {
        self::$callbackFunctions[$eventName] ??= [];
        // @checkme call only once per event even if specified several times?
        self::$callbackFunctions[$eventName][] = $callbackFunc;
    }

    public static function getSubscribedEvents()
    {
        //return [
        //    'workflow.guard' => ['onGuardEvent'],
        //];
        return self::$subscribedEvents;
    }

    public static function getCallbackFunctions()
    {
        return self::$callbackFunctions;
    }

    public static function reset()
    {
        self::$subscribedEvents = [];
        self::$callbackFunctions = [];
    }
}
