<?php

/**
 * @package modules\workflow
 * @category Xaraya Web Applications Framework
 * @version 2.5.7
 * @copyright see the html/credits.html file in this release
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link https://github.com/mikespub/xaraya-modules
**/

namespace Xaraya\Modules\Workflow\UserGui;

use Xaraya\Modules\Workflow\WorkflowConfig;
use Xaraya\Modules\MethodClass;
use xarSecurity;
use xarSession;
use xarMod;
use sys;
use Exception;

sys::import('xaraya.modules.method');

/**
 * workflow user test_queue function
 */
class TestQueueMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * the test queue user function - manually process the transition event queue here
     * @author mikespub
     * @access public
     * @return array|string|void empty
     */
    public function __invoke(array $args = [])
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
        $data['context'] = $this->getContext();
        $data['userId'] = $this->getContext()?->getUserId() ?? xarSession::getVar('role_id');

        if (!empty($data['warning'])) {
            $data['log'] = 'No manual processing';
            return $data;
        }

        // run scheduler job to manually process the queue
        $params = ['scheduled' => 'manual'];
        $data['log'] = xarMod::apiFunc('workflow', 'scheduler', 'transitions', $params, $this->getContext());

        return $data;
    }
}
