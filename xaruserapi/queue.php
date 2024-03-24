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

sys::import('modules.workflow.class.queue');
use Xaraya\Modules\Workflow\WorkflowQueue;

/**
 * the queue user API function
 *
 * @uses WorkflowQueue
 * @author mikespub
 * @access public
 */
function workflow_userapi_queue(array $args = [], $context = null)
{
    return new WorkflowQueue($args, $context);
}
