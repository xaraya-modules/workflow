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
use sys;

sys::import('xaraya.modules.method');

/**
 * workflow admin workitem function
 * @extends MethodClass<AdminGui>
 */
class WorkitemMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * the workitem administration function
     * @author mikespub
     * @access public
     * @see AdminGui::workitem()
     */
    public function __invoke(array $args = [])
    {
        // Security Check
        if (!$this->sec()->checkAccess('AdminWorkflow')) {
            return;
        }

        // Common setup for Galaxia environment
        sys::import('modules.workflow.lib.galaxia.config');
        $tplData = [];

        // Adapted from tiki-g-view_workitem.php

        include_once(GALAXIA_LIBRARY . '/processmonitor.php');

        if (!isset($_REQUEST['itemId'])) {
            $tplData['msg'] =  $this->ml("No item indicated");

            $tplData['context'] ??= $this->getContext();
            return $this->mod()->template('errors', $tplData);
        }

        $wi = $processMonitor->monitor_get_workitem($_REQUEST['itemId']);
        if (is_numeric($wi['user'])) {
            $wi['user'] = $this->user($wi['user'])->getName();
        }
        $tplData['wi'] = &$wi;

        $tplData['stats'] =  $processMonitor->monitor_stats();

        $sameurl_elements = [
            'offset',
            'sort_mode',
            'where',
            'find',
            'itemId',
        ];

        $tplData['mid'] =  'tiki-g-view_workitem.tpl';

        return $tplData;
    }
}
