<?php

/**
 * Workflow Module Marking Trait for Symfony Workflow tests
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

namespace Xaraya\Modules\Workflow\Traits;

use Xaraya\Context\Context;
use Exception;

/**
* For documentation purposes only - available via MarkingTrait
*/
interface MarkingInterface
{
    public function getId(): string;
    public function getMarking(): array|string|null;
    public function setMarking($marking, Context|array $context = []): void;
    public function getContext(): Context|array|null;
    public function setContext(Context|array $context = []): void;
}

trait MarkingTrait
{
    protected $_workflowMarking;  // array for workflow or string for state_machine
    protected $_workflowContext;

    public function getId(): string
    {
        //return spl_object_id($this);
        if (empty($this->objectref)) {
            throw new Exception('Property ' . $this->name . ' of class ' . get_class($this) . ' is missing an objectref to create a valid subjectId for workflows');
        }
        return implode('.', [$this->objectref->name, (string) $this->objectref->itemid]);
    }

    // See https://write.vanoix.com/alexandre/creer-un-workflow-metier-avec-le-composant-symfony-workflow
    //
    // See https://github.com/symfony/symfony/blob/6.3/src/Symfony/Component/Workflow/Tests/Subject.php
    public function getMarking(): array|string|null
    {
        return $this->_workflowMarking;
    }

    public function setMarking($marking, Context|array $context = []): void
    {
        $this->_workflowMarking = $marking;
        if (!empty($context)) {
            $this->setContext($context);
        }
    }

    public function getContext(): Context|array|null
    {
        return $this->_workflowContext;
    }

    public function setContext(Context|array $context = []): void
    {
        // no update of object context with transition context
        $this->_workflowContext = $context;
    }
}
