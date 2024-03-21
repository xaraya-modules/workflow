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

sys::import('modules.workflow.class.subject');
use Xaraya\Modules\Workflow\WorkflowSubject;

/**
 * the subject user API function
 *
 * @uses WorkflowSubject
 * @author mikespub
 * @access public
 */
function workflow_userapi_subject(array $args = [], $context = null)
{
    $args['object'] ??= 'dummy';
    $args['itemid'] ??= 0;
    $subject = new WorkflowSubject($args['object'], (int) $args['itemid']);
    $subject->setContext($context);

    return $subject;
}
