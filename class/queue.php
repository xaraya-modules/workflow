<?php

/**
 * Workflow Module Queue for Symfony Workflow events
 *
 * This keeps a queue of workflow events for later processing - @todo
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

use DataObjectFactory;
use xarLog;

class WorkflowQueue extends WorkflowTracker
{
    protected static $objectName = 'workflow_queue';
    protected static $fieldList = ['workflow', 'user', 'subject', 'event', 'transition', 'marking', 'updated', 'context'];

    public static function init(array $args = []) {}

    public static function addItem(string $workflowName, string $subjectId, string $eventName, string $transition, string $marking, string $context, int $userId)
    {
        $newItem = [
            'workflow' => $workflowName,
            'subject' => $subjectId,
            'user' => $userId,
            'event' => $eventName,
            'transition' => $transition,
            'marking' => $marking,
            'updated' => time(),
            'context' => $context,  // json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ];
        $objectRef = DataObjectFactory::getObject(['name' => static::$objectName]);
        $queueId = $objectRef->createItem($newItem);
        xarLog::message("New queued item $queueId added");
        return $queueId;
    }
}
