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

sys::import('modules.workflow.class.process');
use Xaraya\Modules\Workflow\WorkflowProcess;
use Symfony\Component\Workflow\Dumper\MermaidDumper;

/**
 * the mermaid user API function
 * @todo save dumper output to file to avoid needing sys::autoload() here too
 *
 * See https://github.com/lyrixx/SFLive-Paris2016-Workflow/blob/master/src/Twig/WorkflowExtension.php
 * @todo add interaction? - see https://mermaid.js.org/syntax/flowchart.html#interaction
 *
 * @uses \sys::autoload()
 * @uses WorkflowProcess
 * @return string
 */
function workflow_userapi_mermaid(array $args = [], $context = null)
{
    sys::autoload();
    $args['type'] ??= 'state_machine';
    // see https://github.com/symfony/framework-bundle/blob/7.0/Command/WorkflowDumpCommand.php
    $transitionType = ('workflow' === $args['type']) ? MermaidDumper::TRANSITION_TYPE_WORKFLOW : MermaidDumper::TRANSITION_TYPE_STATEMACHINE;
    $dumper = new MermaidDumper($transitionType);
    $workflow = WorkflowProcess::getProcess($args['workflow']);
    if (!empty($args['subject'])) {
        return $dumper->dump($workflow->getDefinition(), $workflow->getMarking($args['subject']));
    }
    return $dumper->dump($workflow->getDefinition());
}
