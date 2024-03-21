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

sys::import('modules.workflow.class.dumper');
use Xaraya\Modules\Workflow\WorkflowDumper;

/**
 * the dumper user API function
 *
 * @uses WorkflowDumper
 * @author mikespub
 * @access public
 */
function workflow_userapi_dumper(array $args = [], $context = null)
{
    // @todo do something with $args and $context
    return new WorkflowDumper();
}
