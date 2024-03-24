<?php
/**
 * Workflow Module Callback Handlers for Symfony Workflow events
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

use DataObject;
use DataObjectFactory;
use xarCache;
use xarLog;
use xarRoles;
use xarSession;
use sys;
use Exception;

sys::import('modules.workflow.class.base');

class WorkflowHandlers extends WorkflowBase
{
    public static function getObjectRef($subject, $eventName)
    {
        if (method_exists($subject, 'getObject')) {
            return $subject->getObject();
        }
        $subjectId = $subject->getId();
        // @checkme assuming subjectId = objectName.itemId here
        [$objectName, $itemId] = static::fromSubjectId($subjectId);
        $context = null;
        if (method_exists($subject, 'getContext')) {
            $context = $subject->getContext();
        }
        $objectRef = DataObjectFactory::getObject(['name' => $objectName, 'itemid' => $itemId], $context);
        if (empty($objectRef)) {
            $message = "Unknown subject $subjectId";
            xarLog::message("Event $eventName stopped: $message", xarLog::LEVEL_WARNING);
            throw new Exception($message);
        }
        return $objectRef;
    }

    public static function fromSubjectId(string $subjectId)
    {
        return WorkflowTracker::fromSubjectId($subjectId);
    }

    // here you can specify callback functions as transition blockers - expression language is not supported
    public static function guardCheckAdmin(bool $admin, $roleId = null)
    {
        if (empty($admin)) {
            return;
        }
        // @checkme list of admin roles is pre-defined based on default role groups here
        $adminGroups = ['administrators', 'sitemanagers'];
        return static::guardCheckRoles($adminGroups, $roleId);
    }

    // with the corresponding actual function to check yourself e.g. in code or templates
    public static function doCheckAdmin(bool $admin, int $userId)
    {
        if (empty($admin)) {
            return true;
        }
        $adminGroups = ['administrators', 'sitemanagers'];
        return static::doCheckRoles($adminGroups, $userId);
    }

    public static function guardCheckRoles(array $groupUserNames, $roleId = null)
    {
        sys::import('modules.roles.class.roles');
        $parentRoleIds = static::getGroupRoleIds($groupUserNames);
        // @checkme we only look up the direct parents here
        $handler = function ($event, $eventName, $dispatcher) use ($parentRoleIds, $roleId) {
            // @todo use $context if available?
            //$context = $event->getSubject()->getContext();
            $userId = $roleId ?? xarSession::getVar('role_id') ?? 0;
            $parents = xarCache::getParents($userId);
            $intersect = array_intersect($parents, $parentRoleIds);
            if (empty($intersect)) {
                $transitionName = $event->getTransition()->getName();
                $message = "Sorry, you do not have the right roles";
                $event->setBlocked(true, $message);
                xarLog::message("Transition $transitionName blocked: $message", xarLog::LEVEL_INFO);
            }
        };
        return $handler;
    }

    public static function doCheckRoles(array $groupUserNames, int $userId)
    {
        $parentRoleIds = static::getGroupRoleIds($groupUserNames);
        $parents = xarCache::getParents($userId);
        $intersect = array_intersect($parents, $parentRoleIds);
        return !empty($intersect);
    }

    public static function getGroupRoleIds(array $groupUserNames)
    {
        $groupRoleIds = [];
        foreach ($groupUserNames as $uname) {
            $role = xarRoles::ufindRole($uname);
            if (empty($role)) {
                xarLog::message("Unknown role '$uname' to check in workflow transition", xarLog::LEVEL_WARNING);
                continue;
            }
            $groupRoleIds[] = $role->getID();
        }
        return $groupRoleIds;
    }

    public static function guardCheckAccess(string $action, $roleId = null)
    {
        sys::import('modules.dynamicdata.class.objects.factory');
        $handler = function ($event, $eventName, $dispatcher) use ($action, $roleId) {
            $objectRef = static::getObjectRef($event->getSubject(), $eventName);
            // @todo use $context if available?
            //$context = $event->getSubject()->getContext();
            $subjectId = $event->getSubject()->getId();
            $userId = $roleId ?? xarSession::getVar('role_id') ?? 0;
            if (empty($objectRef) || !$objectRef->checkAccess($action, $objectRef->itemid, $userId)) {
                $transitionName = $event->getTransition()->getName();
                $message = "Sorry, you do not have '$action' access to subject '$subjectId' for event '$eventName'";
                $event->setBlocked(true, $message);
                xarLog::message("Transition $transitionName blocked: $message", xarLog::LEVEL_INFO);
            }
        };
        return $handler;
    }

    public static function doCheckAccess(string|DataObject $objectName, int|null $itemId, string $action, int $userId)
    {
        if (is_object($objectName)) {
            $objectRef = $objectName;
            $objectName = $objectRef->name;
            $itemId ??= $objectRef->itemid;
        } else {
            $objectRef = DataObjectFactory::getObject(['name' => $objectName, 'itemid' => $itemId]);
        }
        if (empty($objectRef)) {
            return false;
        }
        return $objectRef->checkAccess($action, $itemId, $userId);
    }

    public static function guardCheckSecurity($mask, $catch = 0, $component = '', $instance = '', $module = '', $rolename = '', $realm = 0, $level = 0)
    {
        sys::import('modules.privileges.class.security');
        // Fallback for checkAccess:
        // return xarSecurity::check($mask,0,'Item',$this->moduleid.':'.$this->itemtype.':'.$itemid,'',$rolename);
    }

    public static function doCheckSecurity()
    {
        throw new Exception("Use xarSecurity::check() yourself :-)");
    }

    // this would be where we check the actual status of the subject, rather than the places
    public static function guardCheckProperty(array $propertyMapping, array $valueMapping = [])
    {
        sys::import('modules.dynamicdata.class.objects.factory');
        $handler = function ($event, $eventName, $dispatcher) use ($propertyMapping, $valueMapping) {
            $transitionName = $event->getTransition()->getName();
            //$places = $event->getMarking()->getPlaces();
            //$subject = $event->getSubject();
            $subjectId = $event->getSubject()->getId();
            $objectRef = static::getObjectRef($event->getSubject(), $eventName);
            if (empty($objectRef)) {
                $message = "Unknown subject $subjectId";
                xarLog::message("Event $eventName stopped: $message", xarLog::LEVEL_WARNING);
                throw new Exception($message);
            }
            // @checkme assuming subjectId = objectName.itemId here
            [$objectName, $itemId] = static::fromSubjectId($subjectId);
            if (!array_key_exists($objectName, $propertyMapping)) {
                $message = "Unexpected subject $subjectId";
                xarLog::message("Event $eventName stopped: $message", xarLog::LEVEL_WARNING);
                throw new Exception($message);
            }
            if (!empty($itemId)) {
                $id = $objectRef->getItem(['itemid' => $itemId]);
            }
            foreach ($propertyMapping[$objectName] as $propertyName => $match) {
                if (!array_key_exists($propertyName, $objectRef->properties)) {
                    $message = "Sorry, this subject does not have property '$propertyName'";
                    $event->setBlocked(true, $message);
                    xarLog::message("Transition $transitionName blocked: $message", xarLog::LEVEL_INFO);
                }
                $value = $objectRef->properties[$propertyName]->value;
                if ($value != $match) {
                    $message = "Sorry, this subject has '$propertyName' = '$value' instead of '$match'";
                    $event->setBlocked(true, $message);
                    xarLog::message("Transition $transitionName blocked: $message", xarLog::LEVEL_INFO);
                }
            }
        };
        return $handler;
    }

    public static function doCheckProperty(string|DataObject $objectName, int|null $itemId, array $propertyMapping)
    {
        if (is_object($objectName)) {
            $objectRef = $objectName;
            $objectName = $objectRef->name;
            $itemId ??= $objectRef->itemid;
        } else {
            $objectRef = DataObjectFactory::getObject(['name' => $objectName, 'itemid' => $itemId]);
        }
        if (!array_key_exists($objectName, $propertyMapping)) {
            return false;
        }
        if (empty($objectRef)) {
            return false;
        }
        if (!empty($itemId)) {
            $id = $objectRef->getItem(['itemid' => $itemId]);
        }
        foreach ($propertyMapping[$objectName] as $propertyName => $match) {
            if (!array_key_exists($propertyName, $objectRef->properties)) {
                return false;
            }
            $value = $objectRef->properties[$propertyName]->value;
            if ($value != $match) {
                return false;
            }
        }
        return true;
    }

    // here you can specify callback functions to update the actual objects once the transition is completed
    // this would be where we update the actual status of the object, rather than the places of the subject
    public static function updateProperty(array $propertyMapping, array $valueMapping = [])
    {
        sys::import('modules.dynamicdata.class.objects.factory');
        $handler = function ($event, $eventName, $dispatcher) use ($propertyMapping, $valueMapping) {
            //$workflowName = $event->getWorkflowName();
            //$subject = $event->getSubject();
            //$transition = $event->getTransition();
            //$marking = $event->getMarking();
            //$metadata = $event->getMetadata();
            $subjectId = $event->getSubject()->getId();
            $objectRef = static::getObjectRef($event->getSubject(), $eventName);
            if (empty($objectRef)) {
                $message = "Unknown subject $subjectId";
                xarLog::message("Event $eventName stopped: $message", xarLog::LEVEL_WARNING);
                throw new Exception($message);
            }
            // @checkme assuming subjectId = objectName.itemId here
            [$objectName, $itemId] = static::fromSubjectId($subjectId);
            if (!array_key_exists($objectName, $propertyMapping)) {
                $message = "Unexpected subject $subjectId";
                xarLog::message("Event $eventName stopped: $message", xarLog::LEVEL_WARNING);
                throw new Exception($message);
            }
            if (!empty($itemId)) {
                $id = $objectRef->getItem(['itemid' => $itemId]);
            }
            $newItem = [];
            foreach ($propertyMapping[$objectName] as $propertyName => $value) {
                $newItem[$propertyName] = $value;
            }
            $id = $objectRef->updateItem($newItem);
        };
        return $handler;
    }

    // this is where we add the successful transition to a new marking to the tracker
    public static function completeTransition(array $deleteTracker = [], $roleId = null)
    {
        return static::setTrackerItem($deleteTracker, $roleId);
    }

    public static function setTrackerItem(array $deleteTracker = [], $roleId = null)
    {
        sys::import('modules.workflow.class.tracker');
        sys::import('modules.workflow.class.history');
        $handler = function ($event, $eventName, $dispatcher) use ($deleteTracker, $roleId) {
            $workflowName = $event->getWorkflowName();
            $subject = $event->getSubject();
            // @checkme assuming subjectId = objectName.itemId here
            [$objectName, $itemId] = static::fromSubjectId((string) $subject->getId());
            // @todo use $context if available?
            //$context = $event->getSubject()->getContext();
            $userId = $roleId ?? xarSession::getVar('role_id') ?? 0;
            $transitionName = $event->getTransition()->getName();
            // @checkme delete tracker at the end of this transition - pass along eventName to completed
            $deleteEventName = "workflow.$workflowName.delete.$transitionName";
            if (!empty($deleteTracker) && !empty($deleteTracker[$deleteEventName])) {
                $trackerId = WorkflowTracker::deleteItem($workflowName, $objectName, (int) $itemId, $subject->getMarking(), (int) $userId);
            } else {
                $trackerId = WorkflowTracker::setItem($workflowName, $objectName, (int) $itemId, $subject->getMarking(), (int) $userId);
            }
            $marking = implode(WorkflowTracker::AND_OPERATOR, array_keys($event->getMarking()->getPlaces()));
            $context = json_encode($event->getContext(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $historyId = WorkflowHistory::addItem((int) $trackerId, $workflowName, $objectName, (int) $itemId, $transitionName, $marking, (string) $context, (int) $userId);
        };
        return $handler;
    }

    /**
     * @deprecated 2.5.0 use WorkflowHandlers::guardCheckProperty() instead
     */
    public static function guardPropertyHandler(array $propertyMapping, array $valueMapping = [])
    {
        return static::guardCheckProperty($propertyMapping, $valueMapping);
    }

    /**
     * @deprecated 2.5.0 use WorkflowHandlers::updateProperty() instead
     */
    public static function updatePropertyHandler(array $propertyMapping, array $valueMapping = [])
    {
        return static::updateProperty($propertyMapping, $valueMapping);
    }
}
