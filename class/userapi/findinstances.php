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
 * workflow userapi findinstances function
 * @extends MethodClass<UserApi>
 */
class FindinstancesMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * find instances with a certain status, activityId and/or max. started date
     *  - now accepts an activityName and processName if you don't have the ids
     * @author mikespub
     * @access public
     */
    public function __invoke(array $args = [])
    {
        // Common setup for Galaxia environment
        sys::import('modules.workflow.lib.galaxia.config');
        include(GALAXIA_LIBRARY . '/processmonitor.php');

        extract($args);
        if (!isset($status)) {
            $status = 'active';
        }
        if (!isset($actstatus)) {
            $actstatus = 'running';
        }

        $where = '';
        $wheres = [];
        // TODO: reformulate this with bindvars
        if (!empty($status)) {
            $wheres[] = "gi.status='" . $status . "'";
        }
        if (!empty($actstatus)) {
            $wheres[] = "gia.status='" . $actstatus . "'";
        }
        if (!empty($activityId)) {
            $wheres[] = "gia.activityId=" . $activityId;
        }
        if (!empty($max_started)) {
            $wheres[] = "gi.started <= " . $max_started;
        }
        if (!empty($activityName) && !empty($processName)) {
            $wheres[] = "ga.name = '" . $activityName . "' AND gp.name = '" . $processName . "'";
        }

        $where = implode(' and ', $wheres);

        $items = $processMonitor->monitor_list_instances(0, -1, 'instanceId_asc', '', $where, []);

        if (isset($items) && isset($items['data'])) {
            return $items['data'];
        } else {
            return [];
        }
    }
}
