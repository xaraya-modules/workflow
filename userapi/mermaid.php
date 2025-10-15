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
use Symfony\Component\Workflow\Dumper\MermaidDumper;
use Xaraya\Modules\Workflow\WorkflowProcess;
use Xaraya\Modules\MethodClass;
use sys;

sys::import('xaraya.modules.method');

/**
 * workflow userapi mermaid function
 * @extends MethodClass<UserApi>
 */
class MermaidMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * the mermaid user API function
     * @todo save dumper output to file to avoid needing sys::autoload() here too
     *
     * See https://github.com/lyrixx/SFLive-Paris2016-Workflow/blob/master/src/Twig/WorkflowExtension.php
     * @todo add interaction? - see https://mermaid.js.org/syntax/flowchart.html#interaction
     * @uses \sys::autoload()
     * @uses WorkflowProcess
     * @return string
     * @see UserApi::mermaid()
     */
    public function __invoke(array $args = [])
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
}
