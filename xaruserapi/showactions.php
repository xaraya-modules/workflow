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
/**
 * show the actions available to you for this workflow, subjectId and place (called via <xar:workflow-actions tag)
 *
 * <xar:workflow-actions name="actions" config="$config" item="$item" title="$item['marking']" template="$item['marking']"/>
 * @param array<string, mixed> $args
 * with
 *     $args['name'] actions
 *     $args['config'] workflows config
 *     $args['item'] workflow tracker item
 *     $args['title'] fieldset legend {marking}
 *     $args['template'] user-showactions[-{marking}].xt
 *     $args['layout'] default layout in template (unused)
 * @param mixed $context not available in template tag
 * @return string
 */
function workflow_userapi_showactions($args, $context = null)
{
    // Security Check
    if (!xarSecurity::check('ReadWorkflow', 0)) {
        return '';
    }

    sys::import('modules.workflow.class.config');
    $tplData = $args;
    if (!isset($tplData['userId'])) {
        // @todo get userId from $item['user'] here?
        $tplData['userId'] = $context?->getUserId() ?? xarSession::getVar('role_id');
    }

    if (!empty($args['item']) && !empty($args['item']['marking']) && str_contains($args['item']['marking'], ',')) {
        $places = explode(',', $args['item']['marking']);
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
