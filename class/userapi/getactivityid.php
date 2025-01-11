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
use sys;
use BadParameterException;

sys::import('xaraya.modules.method');

/**
 * workflow userapi getactivityid function
 * @extends MethodClass<UserApi>
 */
class GetactivityidMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * Addition to the workflow module when there is a need
     * to retrieve the activityid.
     * @author Mike Dunn submitted by Court Shrock
     * @access public
     * @param mixed $activityName the name of the activity you need an id for (required)
     * @return int workflow activityid
     */
    public function __invoke(array $args = [])
    {
        extract($args);

        if (!isset($activityName)) {
            return;
        }

        sys::import('modules.workflow.lib.galaxia.config');
        include(GALAXIA_LIBRARY . '/processmonitor.php');


        $items = $processMonitor->monitor_list_activities(0, -1, 'activityId_asc', $activityName, '', []);
        unset($processMonitor);
        $activityId = '';

        if (is_array($items)) {
            $keyarray = array_keys($items['data']);
            $key = $keyarray[0];
            $activityId = $items['data'][$key]['activityId'];
        }// if

        return $activityId;
    }
}
