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

use Xaraya\Modules\Workflow\UserGui;
use Xaraya\Modules\Workflow\WorkflowConfig;
use Xaraya\Modules\Workflow\WorkflowSubject;
use Xaraya\Modules\MethodClass;
use xarSecurity;
use xarSession;
use xarVar;
use xarTpl;
use sys;
use Exception;

sys::import('xaraya.modules.method');

/**
 * workflow user test function
 * @extends MethodClass<UserGui>
 */
class TestMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * the test user function
     * @author mikespub
     * @access public
     * @return array|string|void empty
     * @see UserGui::test()
     */
    public function __invoke(array $args = [])
    {
        // Security Check
        if (!$this->sec()->checkAccess('ReadWorkflow')) {
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
        $data['config'] = WorkflowConfig::loadConfig();
        $data['context'] = $this->getContext();
        $data['userId'] = $this->getContext()?->getUserId() ?? $this->session()->getUserId();

        $this->var()->find('workflow', $data['workflow']);
        $this->var()->find('trackerId', $data['trackerId']);
        $this->var()->find('subjectId', $data['subjectId']);
        $this->var()->find('place', $data['place']);
        $this->var()->find('transition', $data['transition']);

        if (!empty($data['subjectId'])) {
            sys::import('modules.workflow.class.subject');
            [$objectName, $itemId] = explode('.', $data['subjectId'] . '.0');
            $subject = new WorkflowSubject($objectName, (int) $itemId);
            $subject->setContext($this->getContext());
            $data['objectref'] = $subject->getObject();
        }

        WorkflowConfig::setAutoload();

        // force using twig if not defined yet
        //$data['context']['twig'] ??= true;

        return $data;
    }
}
