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
use Xaraya\Modules\MethodClass;
use xarVar;
use xarController;
use sys;
use BadParameterException;

sys::import('xaraya.modules.method');

/**
 * workflow userapi getitemtypes function
 * @extends MethodClass<UserApi>
 */
class GetitemtypesMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * utility function to retrieve the list of item types of this module (if any)
     * @return array containing the item types and their description
     * @see UserApi::getitemtypes()
     */
    public function __invoke(array $args = [])
    {
        $itemtypes = [];

        // Common setup for Galaxia environment
        sys::import('modules.workflow.lib.galaxia.config');
        include(GALAXIA_LIBRARY . '/processmonitor.php');

        // get all active processes
        $processes = $processMonitor->monitor_list_all_processes('name_asc', "isActive = 1");

        foreach ($processes as $process) {
            $itemtypes[$process['pId']] = ['label' => $this->var()->prep($process['name'] . ' ' . $process['version']),
                'title' => $this->var()->prep($this->ml('View Process')),
                'url'   => $this->mod()->getURL(
                    'user',
                    'activities',
                    ['filter_process' => $process['pId']]
                ),
            ];
        }
        return $itemtypes;
    }
}
