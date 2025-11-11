<?php

/**
 * @package modules\workflow
 * @category Xaraya Web Applications Framework
 * @version 2.5.7
 * @copyright see the html/credits.html file in this release
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link https://github.com/mikespub/xaraya-modules
**/

namespace Xaraya\Modules\Workflow\UserGui;

use Xaraya\Modules\Workflow\UserGui;
use Xaraya\Modules\Workflow\WorkflowConfig;
use Xaraya\Modules\Workflow\WorkflowLogger;
use Xaraya\Modules\Workflow\WorkflowProcess;
use Xaraya\Modules\Workflow\WorkflowSubject;
use Xaraya\Modules\Workflow\WorkflowTracker;
use Xaraya\Modules\MethodClass;
use BadParameterException;
use Exception;

/**
 * workflow user test_run function
 * @extends MethodClass<UserGui>
 */
class TestRunMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * the test user function - run the actual transition in the workflow here
     * @author mikespub
     * @access public
     * @return array|string|void empty
     * @see UserGui::testRun()
     */
    public function __invoke(array $args = [])
    {
        // Security Check
        if (!$this->sec()->checkAccess('ReadWorkflow')) {
            return;
        }

        $data = $args ?? [];
        $data['warning'] = '';
        // @checkme we don't actually need to require composer autoload here
        try {
            WorkflowConfig::checkAutoload();
            //WorkflowConfig::setAutoload();
        } catch (Exception $e) {
            $data['warning'] = nl2br($e->getMessage());
        }
        $data['config'] = WorkflowConfig::loadConfig();
        $data['context'] = $this->getContext();
        $data['userId'] = $this->getContext()?->getUserId() ?? $this->user()->getId();

        $this->var()->find('workflow', $data['workflow']);
        $this->var()->find('trackerId', $data['trackerId']);
        $this->var()->find('subjectId', $data['subjectId']);
        $this->var()->find('place', $data['place']);
        $this->var()->find('transition', $data['transition']);

        $invalid = [];
        if (empty($data['workflow'])) {
            $invalid[] = 'workflow';
        }
        if (empty($data['trackerId']) && empty($data['subjectId'])) {
            $invalid[] = 'trackerId or subjectId';
        }
        if (!empty($invalid)) {
            $msg = 'Invalid #(1) for #(2) function #(3)() in module #(4)';
            $vars = [join(', ', $invalid), 'user', 'test_run', 'workflow'];
            throw new BadParameterException($vars, $msg);
        }


        if (!empty($data['trackerId'])) {
            $item = WorkflowTracker::getTrackerItem($data['trackerId']);
            if (empty($item)) {
                $msg = 'Invalid #(1) for #(2) function #(3)() in module #(4)';
                $vars = ['trackerId', 'user', 'test_run', 'workflow'];
                throw new BadParameterException($vars, $msg);
            }
            $data['place'] = $item['marking'];
        } elseif (!empty($data['subjectId'])) {
            [$objectName, $itemId] = WorkflowTracker::fromSubjectId($data['subjectId']);
            if (empty($objectName) || empty($itemId)) {
                $msg = 'Invalid #(1) for #(2) function #(3)() in module #(4)';
                $vars = ['subjectId', 'user', 'test_run', 'workflow'];
                throw new BadParameterException($vars, $msg);
            }
            $items = WorkflowTracker::getSubjectItems($data['subjectId'], $data['workflow'], $data['userId']);
            if (!empty($items)) {
                // @checkme there can be only one :-)
                $item = array_values($items)[0];
                $data['place'] = $item['marking'];
                $data['trackerId'] = $item['id'];
            } else {
                $data['place'] = WorkflowConfig::getInitialMarking($data['workflow']);
                $item = ['id' => 0, 'workflow' => $data['workflow'], 'object' => $objectName, 'item' => $itemId, 'user' => $data['userId'], 'marking' => $data['place'], 'updated' => time()];
            }
        } else {
            //$subject = new WorkflowSubject();
            // initiate workflow
            //$marking = $workflow->getMarking($subject);
            throw new Exception('How did we end up here?');
        }

        // <xar:workflow-actions name="actions" config="$config" item="$item" title="$item['marking']" template="$item['marking']"/>
        if (empty($data['transition'])) {
            $data['item'] = $item;
            return $data;
        }

        // @checkme we DO actually need to require composer autoload here
        try {
            //WorkflowConfig::checkAutoload();
            WorkflowConfig::setAutoload();
        } catch (Exception $e) {
            $data['warning'] = nl2br($e->getMessage());
            return $this->mod()->template('test', $data);
        }

        WorkflowProcess::setLogger(new WorkflowLogger());

        $workflow = WorkflowProcess::getProcess($data['workflow']);

        // @todo verify use of Xaraya $this->getContext() with Symfony Workflow component
        $subject = new WorkflowSubject($item['object'], (int) $item['item']);
        $subject->setContext($this->getContext());
        if (!empty($data['trackerId'])) {
            // set current marking
            if (WorkflowProcess::isStateMachine($workflow)) {
                $subject->setMarking($item['marking'], $item);
            } else {
                $places = explode(WorkflowTracker::AND_OPERATOR, $item['marking']);
                $marking = [];
                foreach ($places as $here) {
                    $marking[$here] = 1;
                }
                $subject->setMarking($marking, $item);
            }
        } else {
            // initiate workflow for this subject
            $marking = $workflow->getMarking($subject);
            //$places = implode(', ', $marking->getPlaces());
        }

        $transitions = $workflow->getEnabledTransitions($subject);
        // request transition
        if ($workflow->can($subject, $data['transition'])) {
            $marking = $workflow->apply($subject, $data['transition'], $args);
            $places = implode(', ', array_keys($marking->getPlaces()));
            $data['message'] = "The transition of subject " . $subject->getId() . " to " . WorkflowConfig::formatName($places) . " was successful";
        } else {
            $blockers = $workflow->buildTransitionBlockerList($subject, $data['transition']);
            $msg = 'Invalid #(1) for #(2) function #(3)() in module #(4)';
            foreach ($blockers as $blocker) {
                $msg .= "\nBlocker: " . $blocker->getMessage();
            }
            $vars = ['transition', 'user', 'test_run', 'workflow'];
            throw new BadParameterException($vars, $msg);
        }
        $data['objectref'] = $subject->getObject();
        unset($data['place']);

        return $this->mod()->template('test', $data);
    }
}
