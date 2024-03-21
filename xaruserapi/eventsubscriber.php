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

sys::import('modules.workflow.class.eventsubscriber');
use Xaraya\Modules\Workflow\WorkflowEventSubscriber;

/**
 * the eventsubscriber user API function
 *
 * @uses WorkflowEventSubscriber
 * @author mikespub
 * @access public
 */
function workflow_userapi_eventsubscriber(array $args = [], $context = null)
{
    return new WorkflowEventSubscriber($args, $context);
}
