<?php

/**
 * @package modules\workflow
 * @category Xaraya Web Applications Framework
 * @version 2.5.7
 * @copyright see the html/credits.html file in this release
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link https://github.com/mikespub/xaraya-modules
**/

namespace Xaraya\Modules\Workflow\UserApi;


use Xaraya\Modules\Workflow\UserApi;
use Xaraya\Modules\Workflow\WorkflowConfig;
use Xaraya\Modules\Workflow\WorkflowProcess;
use Xaraya\Modules\Workflow\WorkflowSubject;
use Xaraya\Modules\Workflow\WorkflowTracker;
use Xaraya\Modules\MethodClass;
use xarSecurity;
use xarLog;
use xarMod;
use DataObjectDescriptor;
use sys;
use BadParameterException;

sys::import('xaraya.modules.method');

/**
 * workflow userapi run_transition function
 * @extends MethodClass<UserApi>
 */
class RunTransitionMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * the run transition user API function - used by hook calls to workflow
     * @author mikespub
     * @access public
     */
    public function __invoke(array $args = [])
    {
        // Security Check
        if (!xarSecurity::check('ReadWorkflow')) {
            return;
        }
        $workflowName = $args['workflow'];
        $subjectId = $args['subjectId'] ?? null;
        $transitionName = $args['transition'];
        // @checkme let the event handler called for this transition know that we have been scheduled (or not)
        //$args['scheduled'] ??= false;

        if (!WorkflowConfig::hasWorkflowConfig($workflowName)) {
            xarLog::message("No workflow found for '$transitionName' in '$workflowName'", xarLog::LEVEL_INFO);
            return false;
        }

        // if we come from a hook function
        if (empty($subjectId)) {
            sys::import('modules.dynamicdata.class.objects.descriptor');
            $moduleName = $args['module'] ?? xarMod::getName();
            $itemType = $args['itemtype'] ?? 0;
            $itemId = $args['itemid'] ?? 0;
            $moduleId = $args['module_id'] ?? xarMod::getRegID($moduleName);
            $info = DataObjectDescriptor::getObjectID(['moduleid' => $moduleId, 'itemtype' => $itemType]);
            if (empty($info) || empty($info['name'])) {
                xarLog::message("No object associated with module '$moduleName' ($moduleId) itemtype '$itemType' for '$transitionName' in '$workflowName'", xarLog::LEVEL_INFO);
                // @checkme create fake objectName for module:itemtype if no object is available for now?
                //return false;
                $objectName = "$moduleName:$itemType";
            } else {
                $objectName = $info['name'];
            }
            $subjectId = WorkflowTracker::toSubjectId($objectName, $itemId);
        } else {
            [$objectName, $itemId] = WorkflowTracker::fromSubjectId($subjectId);
        }
        xarLog::message("We will trigger '$transitionName' for '$subjectId' in '$workflowName' here...", xarLog::LEVEL_INFO);

        // @checkme we DO actually need to require composer autoload here
        WorkflowConfig::setAutoload();

        sys::import('modules.workflow.class.logger');
        sys::import('modules.workflow.class.process');
        sys::import('modules.workflow.class.subject');
        //WorkflowProcess::setLogger(new WorkflowLogger());

        $workflow = WorkflowProcess::getProcess($workflowName);

        // @todo verify use of Xaraya $this->getContext() with Symfony Workflow component
        $subject = new WorkflowSubject($objectName, (int) $itemId);
        $subject->setContext($this->getContext());
        // @checkme since we don't verify the state of the original object here, this will be triggered for
        // each hook event even if it has already been hooked before. So we will get the same trackerId as
        // before (= same workflow, subject and user), but different entries in the history table...
        // initiate workflow for this subject
        $marking = $workflow->getMarking($subject);

        //$transitions = $workflow->getEnabledTransitions($subject);
        // request transition
        if ($workflow->can($subject, $transitionName)) {
            $marking = $workflow->apply($subject, $transitionName, $args);
            //$places = implode(', ', array_keys($marking->getPlaces()));
        } else {
            $blockers = $workflow->buildTransitionBlockerList($subject, $transitionName);
            $msg = 'Invalid #(1) for #(2) function #(3)() in module #(4)';
            foreach ($blockers as $blocker) {
                $msg .= "\nBlocker: " . $blocker->getMessage();
            }
            $vars = ['transition', 'user', 'run_transition', 'workflow'];
            throw new BadParameterException($vars, $msg);
        }

        return true;
    }
}
