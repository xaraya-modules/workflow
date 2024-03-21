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

sys::import('modules.workflow.class.logger');
use Xaraya\Modules\Workflow\WorkflowLogger;

/**
 * the logger user API function
 *
 * @uses WorkflowLogger
 * @author mikespub
 * @access public
 */
function workflow_userapi_logger(array $args = [], $context = null)
{
    // @todo do something with $args and $context
    return new WorkflowLogger();
}
