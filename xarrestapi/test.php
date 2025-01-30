<?php
/**
 * Workflow Module REST API for Symfony Workflow Component (test)
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

use Xaraya\Modules\Workflow\WorkflowConfig;
use Xaraya\Modules\Workflow\WorkflowProcess;
use Xaraya\Modules\Workflow\WorkflowSubject;
use Xaraya\Modules\Workflow\WorkflowTracker;
use Xaraya\Modules\Workflow\WorkflowHistory;

/**
 * Sample REST API call supported by this module (if any)
 *
 * @return array|string of info
 */
function workflow_restapi_test($args = [], $context = null)
{
    xarLog::init();
    $userId = $context?->getUserId() ?? xarSession::getUserId();
    // @checkme pass all args from handler here?
    //extract($args);
    $result = $args;
    $what = $args['what'] ?? '';
    switch ($what) {
        case 'config':
            sys::import('modules.workflow.class.config');
            $config = WorkflowConfig::loadConfig();
            $workflowName = $args['workflow'] ?? '';
            if (empty($workflowName)) {
                return $config;
            }
            if (empty($config[$workflowName])) {
                return "Invalid workflow '$workflowName'";
            }
            $result = $config[$workflowName];
            break;
        case 'process':
            sys::import('modules.workflow.class.process');
            $workflowName = $args['workflow'] ?? '';
            $subjectId = $args['subjectId'] ?? '';
            $transition = $args['transition'] ?? '';
            $workflow = WorkflowProcess::getProcess($workflowName);
            $result = WorkflowProcess::showProcess($workflow);
            break;
        case 'tracker':
            sys::import('modules.workflow.class.tracker');
            $workflowName = $args['workflow'] ?? '';
            $subjectId = $args['subjectId'] ?? '';
            $trackerId = $args['trackerId'] ?? 0;
            $paging = [];
            // @checkme we don't support filter here since we're already filtering in tracker
            //$allowed = array_flip(['order', 'offset', 'limit', 'filter', 'count', 'access']);
            $paging_params = ['order', 'offset', 'limit', 'count'];
            foreach ($paging_params as $param) {
                if (!empty($args[$param])) {
                    $paging[$param] = $args[$param];
                }
            }
            if (!empty($paging)) {
                //$paging['count'] = true;
                WorkflowTracker::setPaging($paging);
            }
            if (!empty($trackerId)) {
                $result = WorkflowTracker::getTrackerItem($trackerId);
            } elseif (!empty($subjectId)) {
                $result = WorkflowTracker::getSubjectItems($subjectId, $workflowName, $userId);
            } else {
                $result = WorkflowTracker::getWorkflowItems($workflowName, $userId);
            }
            //$result['paging'] = $paging;
            //$result['paging']['count'] = WorkflowTracker::getCount();
            break;
        case 'history':
            sys::import('modules.workflow.class.history');
            $workflowName = $args['workflow'] ?? '';
            $subjectId = $args['subjectId'] ?? '';
            $trackerId = $args['trackerId'] ?? 0;
            $paging = [];
            // @checkme we don't support filter here since we're already filtering in tracker
            //$allowed = array_flip(['order', 'offset', 'limit', 'filter', 'count', 'access']);
            $paging_params = ['order', 'offset', 'limit', 'count'];
            foreach ($paging_params as $param) {
                if (!empty($args[$param])) {
                    $paging[$param] = $args[$param];
                }
            }
            if (!empty($paging)) {
                //$paging['count'] = true;
                WorkflowHistory::setPaging($paging);
            }
            if (!empty($trackerId)) {
                $result = WorkflowHistory::getTrackerItems($trackerId);
            } elseif (!empty($subjectId)) {
                $result = WorkflowHistory::getSubjectItems($subjectId, $workflowName, $userId);
            } else {
                $result = WorkflowHistory::getWorkflowItems($workflowName, $userId);
            }
            //$result['paging'] = $paging;
            //$result['paging']['count'] = WorkflowHistory::getCount();
            break;
        case 'subject':
            sys::import('modules.workflow.class.subject');
            $objectName = $args['object'] ?? '';
            $itemId = $args['item'] ?? 0;
            $subject = new WorkflowSubject($objectName, (int) $itemId);
            $subject->setContext($context);
            $result = [
                'marking' => $subject->getMarking(),
                'context' => $subject->getContext(),
                'objectref' => $subject->getObject()->getFieldValues([], 1),
                'descriptor' => $subject->getObject()->descriptor->getArgs(),
            ];
            break;
        default:
            break;
    }
    //xarVar::fetch('name', 'isset', $name, null, xarVar::NOT_REQUIRED);
    return $result;
}
