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
use Xaraya\Services\xar;

$baseDir = dirname(__DIR__);
$baseDir = '/home/mikespub/xaraya-core';
require_once $baseDir . '/vendor/autoload.php';
// initialize bootstrap
sys::init();
// initialize caching - delay until we need results
xar::cache()->init();
// initialize loggers
//xar::log()->init();
// initialize database - delay until caching fails
xar::db()->init();
// initialize modules
//xar::mod()->init();
// initialize users
//xar::user()->init();
//sys::import('modules.workflow.class.logger');

use Xaraya\Context\Context;
use Xaraya\Context\SessionContext;
use Xaraya\Modules\Workflow\WorkflowLogger;
use Xaraya\Modules\Workflow\WorkflowProcess;
use Xaraya\Modules\Workflow\WorkflowSubject;
use Xaraya\Modules\Workflow\WorkflowTracker;
use Symfony\Component\Workflow\Workflow;

//WorkflowProcess::setLogger(new WorkflowLogger());

//$sitePrefix = '/bermuda';
//echo WorkflowProcess::dumpProcess('hook_sample', $sitePrefix);
//xar::exit();
$workflow = WorkflowProcess::getProcess('cd_loans');

// start session
xar::session()->setSessionClass(SessionContext::class);
xar::session()->start();

// start session with userId
$xarayaContext = new Context(['hello' => 'world']);
xar::session()->getInstance()->startSession($xarayaContext, 'phpunit', 6);

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
    xar::exit();
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
    xar::exit();
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
