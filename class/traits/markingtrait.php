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
use Xaraya\Context\ContextInterface;
use Xaraya\Context\ContextTrait;

/**
* For documentation purposes only - available via MarkingTrait
*/
interface MarkingInterface extends ContextInterface
{
    public function getId(): string;
    public function getMarking(): array|string|null;
    public function setMarking($marking, Context|array $context = []): void;
}

trait MarkingTrait
{
    use ContextTrait;

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
            if (!($context instanceof Context)) {
                $context = new Context($context);
            }
            $this->setContext($context);
        }
    }

    /**
     * @return ?Context<string, mixed>
     */
    public function getContext()
    {
        return $this->_workflowContext;
    }

    /**
     * @param ?Context<string, mixed> $context
     * @return void
     */
    public function setContext($context)
    {
        // no update of object context with transition context
        $this->_workflowContext = $context;
    }
}
