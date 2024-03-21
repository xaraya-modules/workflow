<?php
/**
 * Workflow Module
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

sys::import('modules.workflow.class.transition');
use Xaraya\Modules\Workflow\WorkflowTransition;

/**
 * the transition user API function
 *
 * @uses WorkflowTransition
 * @author mikespub
 * @access public
 */
function workflow_userapi_transition(array $args = [], $context = null)
{
    return new WorkflowTransition($args, $context);
}
