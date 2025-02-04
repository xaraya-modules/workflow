<?php

/**
 * Workflow Module Test Script for Symfony Workflow tests
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

if (php_sapi_name() !== 'cli') {
    echo 'Workflow Module Test Script for Symfony Workflow tests';
    return;
}

$baseDir = dirname(__DIR__);
$baseDir = '/home/mikespub/xaraya-core';
require_once $baseDir . '/vendor/autoload.php';
// initialize bootstrap
sys::init();
// initialize caching - delay until we need results
xarCache::init();
// initialize loggers
xarLog::init();
// initialize database - delay until caching fails
xarDatabase::init();
// initialize modules
//xarMod::init();
// initialize users
//xarUser::init();
sys::import('modules.workflow.class.process');
sys::import('modules.workflow.class.subject');
sys::import('modules.workflow.class.tracker');
//sys::import('modules.workflow.class.logger');

use Xaraya\Modules\Workflow\WorkflowConfig;
use Xaraya\Modules\Workflow\WorkflowLogger;
use Xaraya\Modules\Workflow\WorkflowProcess;
use Xaraya\Modules\Workflow\WorkflowSubject;
use Xaraya\Modules\Workflow\WorkflowTracker;
use Xaraya\Sessions\SessionHandler;
use Symfony\Component\Workflow\Workflow;

//WorkflowProcess::setLogger(new WorkflowLogger());

//$sitePrefix = '/bermuda';
//echo WorkflowProcess::dumpProcess('hook_sample', $sitePrefix);
//xarCore::exit();
$workflow = WorkflowProcess::getProcess('cd_loans');

// initialize session
xarSession::init();
$_SESSION[SessionHandler::PREFIX . 'role_id'] = 6;

$subject = new WorkflowSubject('cdcollection', 5);

// initiate workflow
$marking = $workflow->getMarking($subject);
echo "Marking: " . var_export($marking, true) . "\n";
$transitions = $workflow->getEnabledTransitions($subject);
echo "Transitions: " . var_export($transitions, true) . "\n";
foreach ($transitions as $transition) {
    echo "Transition '" . $transition->getName() . "': from '" . implode("', '", $transition->getFroms()) . "' to '" . implode("', '", $transition->getTos()) . "'\n";
}
$transition = "request";
try {
    $result = $workflow->can($subject, $transition);
} catch (Exception $e) {
    echo $e->getMessage();
    xarCore::exit();
    return;
}
echo "Result: " . var_export($result, true) . "\n";
$subjectId = $subject->getId();
echo "SubjectId: " . var_export($subjectId, true) . "\n";
if (!$result) {
    $blockers = $workflow->buildTransitionBlockerList($subject, $transition);
    $msg = 'Invalid transition ' . $transition;
    foreach ($blockers as $blocker) {
        $msg .= "\nBlocker: " . $blocker->getMessage();
    }
    echo $msg;
    xarCore::exit();
    return;
}
$marking = $workflow->apply($subject, "request", [Workflow::DISABLE_ANNOUNCE_EVENT => true]);
echo "Marking: " . var_export($marking, true) . "\n";
$context = $subject->getContext();
echo "Context: " . var_export($context, true) . "\n";
$transition = "approve";
$transitions = $workflow->getEnabledTransition($subject, $transition);
//$transitions = $workflow->buildTransitionBlockerList($subject, "approve");
echo "Transitions: " . var_export($transitions, true) . "\n";
$items = WorkflowTracker::getItems("cd_loans", "cdcollection", 0, '', 6);
echo "Items: " . var_export($items, true) . "\n";
$todo = [];
foreach ($items as $id => $item) {
    $todo[$item['object']] ??= [];
    $todo[$item['object']][] = ((int) $item['item'] > 20) ? ((int) $item['item'] - 20) : (int) $item['item'];
}
echo "Todo: " . var_export($todo, true) . "\n";
foreach ($todo as $object => $itemids) {
    //$values = WorkflowTracker::getObjectValues($object, $itemids, ['status']);
    $values = WorkflowTracker::getObjectValues($object, $itemids);
    echo "Values: " . var_export($values, true) . "\n";
}
