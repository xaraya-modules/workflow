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
use Xaraya\Modules\Workflow\WorkflowSubject;
use Xaraya\Bridge\TemplateEngine\TwigBridge;

/**
 * the test user function
 *
 * @author mikespub
 * @access public
 * @return array|string|void empty
 */
function workflow_user_test(array $args = [], $context = null)
{
    // Security Check
    if (!xarSecurity::check('ReadWorkflow')) {
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
    $data['context'] = $context;
    $data['userId'] = $context?->getUserId() ?? xarSession::getVar('role_id');

    xarVar::fetch('workflow', 'isset', $data['workflow'], null, xarVar::NOT_REQUIRED);
    xarVar::fetch('trackerId', 'isset', $data['trackerId'], null, xarVar::NOT_REQUIRED);
    xarVar::fetch('subjectId', 'isset', $data['subjectId'], null, xarVar::NOT_REQUIRED);
    xarVar::fetch('place', 'isset', $data['place'], null, xarVar::NOT_REQUIRED);
    xarVar::fetch('transition', 'isset', $data['transition'], null, xarVar::NOT_REQUIRED);

    if (!empty($data['subjectId'])) {
        sys::import('modules.workflow.class.subject');
        [$objectName, $itemId] = explode('.', $data['subjectId'] . '.0');
        $subject = new WorkflowSubject($objectName, (int) $itemId);
        $subject->setContext($context);
        $data['objectref'] = $subject->getObject();
    }

    WorkflowConfig::setAutoload();

    /**
     * This is handled automatically by xarTpl::module() now,
     * as long as we pass it the context incl. twig (or not)
     *
    // add paths for Twig filesystem loader (with namespace)
    // {{ include('@workflow/includes/trackeritem.html.twig') }}
    $paths = [
        'code/modules/workflow/templates' => 'workflow',
    ];
    // override default options for Twig environment
    $options = [
        //'cache' => sys::varpath() . '/cache/templates',
        'debug' => true,
    ];
    // get $context from GUI/API function call or DataObject

    $twigbridge = new TwigBridge($paths, $options, $context);
    $twig = $twigbridge->getEnvironment();

    $template = $twig->load('@workflow/test.html.twig');
    return $template->render($data);
     */
    // force using twig if not defined yet
    //$data['context']['twig'] ??= true;

    return $data;
}
