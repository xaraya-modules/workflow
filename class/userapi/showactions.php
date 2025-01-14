<?php

/**
 * @package modules\workflow
 * @category Xaraya Web Applications Framework
 * @version 2.5.7
 * @copyright see the html/credits.html file in this release
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link https://github.com/mikespub/xaraya-modules
**/

namespace Xaraya\Modules\Workflow\UserApi;


use Xaraya\Modules\Workflow\UserApi;
use Xaraya\Modules\Workflow\WorkflowTracker;
use Xaraya\Modules\MethodClass;
use xarSecurity;
use xarSession;
use xarTpl;
use sys;
use BadParameterException;

sys::import('xaraya.modules.method');

/**
 * workflow userapi showactions function
 * @extends MethodClass<UserApi>
 */
class ShowactionsMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * show the actions available to you for this workflow, subjectId and place (called via <xar:workflow-actions tag)
     * <xar:workflow-actions name="actions" config="$config" item="$item" title="$item['marking']" template="$item['marking']"/>
     * @param array<string,mixed> $args
     *     $args['name'] actions
     *     $args['config'] workflows config
     *     $args['item'] workflow tracker item
     *     $args['title'] fieldset legend {marking}
     *     $args['template'] user-showactions[-{marking}].xt
     *     $args['layout'] default layout in template (unused)
     * @param mixed $this->getContext() not available in template tag
     * @return string
     */
    public function __invoke(array $args = [])
    {
        // Security Check
        if (!$this->checkAccess('ReadWorkflow', 0)) {
            return '';
        }

        $tplData = $args;
        if (!isset($tplData['userId'])) {
            // @todo get userId from $item['user'] here?
            $tplData['userId'] = $this->getContext()?->getUserId() ?? xarSession::getVar('role_id');
        }
        $tplData['context'] ??= $this->getContext();

        if (!empty($args['item']) &&
            !empty($args['item']['marking']) &&
            str_contains($args['item']['marking'], WorkflowTracker::AND_OPERATOR)) {
            $places = explode(WorkflowTracker::AND_OPERATOR, $args['item']['marking']);
            $output = '';
            foreach ($places as $here) {
                $tplData['title'] = ucwords(str_replace('_', ' ', $here));
                $tplData['item']['marking'] = $here;
                $output .= xarTpl::module('workflow', 'user', 'showactions', $tplData, $here);
            }
            return $output;
        }

        if (!empty($args['template'])) {
            return xarTpl::module('workflow', 'user', 'showactions', $tplData, $args['template']);
        } else {
            return xarTpl::module('workflow', 'user', 'showactions', $tplData);
        }
    }
}
