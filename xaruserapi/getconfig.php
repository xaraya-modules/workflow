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

sys::import('modules.workflow.class.config');
use Xaraya\Modules\Workflow\WorkflowConfig;

/**
 * the getconfig user API function
 *
 * @uses WorkflowConfig::loadConfig()
 * @author mikespub
 * @access public
 */
function workflow_userapi_getconfig(array $args = [], $context = null)
{
    return WorkflowConfig::loadConfig();
}
