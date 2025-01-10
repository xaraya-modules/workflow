<?php

/**
 * Workflow Module Test Subject for Symfony Workflow tests - could use TransitionTrait too
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
use sys;

sys::import('modules.workflow.class.base');
sys::import('modules.workflow.class.traits.markingtrait');
sys::import('modules.workflow.class.traits.transitiontrait');

// @todo verify interface with WorkflowBase
class WorkflowSubject implements Traits\MarkingInterface
{
    // @todo verify use of Xaraya $context with Symfony Workflow component
    use Traits\MarkingTrait;

    // @checkme create minimal objectref object for use in getId()
    public $objectref;
    public $name = 'subject';

    public function __construct(string $objectName = 'dummy', int $itemId = 0)
    {
        $this->objectref = (object) ['name' => $objectName, 'itemid' => $itemId];
    }

    public function getObject(bool $build = true)
    {
        sys::import('modules.dynamicdata.class.objects.base');
        // @checkme create fake objectName for module:itemtype if no object is available for now?
        if ($build && !$this->objectref instanceof DataObject && strpos($this->objectref->name, ':') === false) {
            $objectref = DataObjectFactory::getObject(
                ['name' => $this->objectref->name,
                    'itemid' => $this->objectref->itemid],
                // @todo make sure we have a Xaraya context here, and not a Symfony one set by transition
                $this->getContext()
            );
            if (!empty($objectref)) {
                if (!empty($this->objectref->itemid)) {
                    $objectref->getItem();
                }
                $this->objectref = $objectref;
            }
        }
        return $this->objectref;
    }
}

class WorkflowSubjectWithTransitions extends WorkflowSubject  // implements Traits\TransitionInterface
{
    use Traits\TransitionTrait;

    public function getWorkflow(string $workflowName): mixed
    {
        // @todo get the actual workflow here instead of the workflowsproperty config
        return $this->workflows[$workflowName];
    }
}
