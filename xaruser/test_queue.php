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
use Xaraya\Modules\Workflow\WorkflowConfig;

/**
 * the test queue user function - manually process the transition event queue here
 *
 * @author mikespub
 * @access public
 * @return array|string|void empty
 */
function workflow_user_test_queue(array $args = [], $context = null)
{
    // Security Check
    if (!xarSecurity::check('AdminWorkflow')) {
        return;
    }

    $data = $args ?? [];
    $data['warning'] = '';
    // @checkme we don't actually need to require composer autoload here
    sys::import('modules.workflow.class.config');
    try {
        WorkflowConfig::checkAutoload();
        //WorkflowConfig::setAutoload();
    } catch (Exception $e) {
        $data['warning'] = nl2br($e->getMessage());
    }
    $data['context'] = $context;
    $data['userId'] = $context?->getUserId() ?? xarSession::getVar('role_id');

    if (!empty($data['warning'])) {
        $data['log'] = 'No manual processing';
        return $data;
    }

    // run scheduler job to manually process the queue
    $params = ['scheduled' => 'manual'];
    $data['log'] = xarMod::apiFunc('workflow', 'scheduler', 'transitions', $params, $context);

    return $data;
}
