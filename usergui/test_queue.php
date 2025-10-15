<?php

/**
 * @package modules\workflow
 * @category Xaraya Web Applications Framework
 * @version 2.6.2
 * @copyright see the html/credits.html file in this release
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link https://github.com/mikespub/xaraya-modules
**/

namespace Xaraya\Modules\Workflow\UserGui;

use Xaraya\Modules\Workflow\UserGui;
use Xaraya\Modules\Workflow\UserApi;
use Xaraya\Modules\Workflow\SchedulerApi;
use Xaraya\Modules\Workflow\WorkflowConfig;
use Xaraya\Modules\MethodClass;
use sys;
use Exception;

sys::import('xaraya.modules.method');

/**
 * workflow user test_queue function
 * @extends MethodClass<UserGui>
 */
class TestQueueMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * the test queue user function - manually process the transition event queue here
     * @author mikespub
     * @access public
     * @return array|string|void empty
     * @see UserGui::testQueue()
     */
    public function __invoke(array $args = [])
    {
        /** @var UserApi $userapi */
        $userapi = $this->userapi();
        /** @var SchedulerApi $schedulerapi */
        $schedulerapi = $userapi->schedulerapi();
        // Security Check
        if (!$this->sec()->checkAccess('AdminWorkflow')) {
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
        $data['userId'] = $this->getContext()?->getUserId() ?? $this->user()->getId();

        if (!empty($data['warning'])) {
            $data['log'] = 'No manual processing';
            return $data;
        }

        // run scheduler job to manually process the queue
        $params = ['scheduled' => 'manual'];
        $data['log'] = $schedulerapi->transitions($params);

        return $data;
    }
}
