<?php

/**
 * @package modules\workflow
 * @category Xaraya Web Applications Framework
 * @version 2.5.7
 * @copyright see the html/credits.html file in this release
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link https://github.com/mikespub/xaraya-modules
**/

namespace Xaraya\Modules\Workflow\AdminGui;


use Xaraya\Modules\Workflow\AdminGui;
use Xaraya\Modules\MethodClass;
use xarSecurity;
use xarVar;
use sys;
use BadParameterException;

sys::import('xaraya.modules.method');

/**
 * workflow admin export_process function
 * @extends MethodClass<AdminGui>
 */
class ExportProcessMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * the save process administration function
     * @author mikespub
     * @access public
     */
    public function __invoke(array $args = [])
    {
        // Security Check
        if (!xarSecurity::check('AdminWorkflow')) {
            return;
        }

        $data = [];
        if (!xarVar::fetch('pid', 'int', $data['processid'], 0, xarVar::NOT_REQUIRED)) {
            return;
        }

        // Common setup for Galaxia environment
        sys::import('modules.workflow.lib.galaxia.config');
        $tplData = [];

        // Adapted from tiki-g-save_process.php

        include_once(GALAXIA_LIBRARY . '/processmanager.php');

        // The galaxia process manager PHP script.

        // Check if we are editing an existing process
        // if so retrieve the process info and assign it.
        if (!isset($_REQUEST['pid'])) {
            $_REQUEST['pid'] = 0;
        }

        $data['xml'] = htmlentities($processManager->serialize_process($_REQUEST['pid']));
        $data['fields'] = $processManager->get_process($_REQUEST['pid']);
        return $data;
    }
}
