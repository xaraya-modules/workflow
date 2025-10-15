<?php

/**
 * @package modules\workflow
 * @category Xaraya Web Applications Framework
 * @version 2.5.7
 * @copyright see the html/credits.html file in this release
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link https://github.com/mikespub/xaraya-modules
**/

namespace Xaraya\Modules\Workflow\AdminApi;

use Xaraya\Modules\Workflow\AdminApi;
use Xaraya\Modules\Workflow\UserApi;
use Xaraya\Modules\MethodClass;
use sys;

sys::import('xaraya.modules.method');

/**
 * workflow adminapi createhook function
 * @extends MethodClass<AdminApi>
 */
class CreatehookMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * start a create activity for a module item - hook for ('item','create','GUI')
     * @param array<mixed> $args
     * @var mixed $objectid ID of the object
     * @var mixed $extrainfo extra information
     * @return array extrainfo array
     * @see AdminApi::createhook()
     */
    public function __invoke(array $args = [])
    {
        extract($args);
        /** @var UserApi $userapi */
        $userapi = $this->userapi();

        // everything is already validated in HookSubject, except possible empty objectid/itemid for create/display
        $modname = $extrainfo['module'];
        $itemtype = $extrainfo['itemtype'];
        $itemid = $extrainfo['itemid'];
        $modid = $extrainfo['module_id'];

        // see if we need to start some workflow activity here
        if (!empty($itemtype)) {
            $activityId = $this->mod()->getVar("$modname.$itemtype.create");
        }
        if (empty($activityId)) {
            $activityId = $this->mod()->getVar("$modname.create");
        }
        if (empty($activityId)) {
            $activityId = $this->mod()->getVar('default.create');
        }
        if (empty($activityId)) {
            return $extrainfo;
        }

        // Symfony Workflow transition
        if (!is_numeric($activityId) && strpos($activityId, '/') !== false) {
            [$workflowName, $transitionName] = explode('/', $activityId);
            if (!$userapi->run_transition(['workflow' => $workflowName,
                'subjectId' => null,
                'transition' => $transitionName,
                // extra parameters from hook functions
                'hooktype' => 'ItemCreate',
                'module' => $modname,
                'itemtype' => $itemtype,
                'itemid' => $objectid,
                'module_id' => $modid,
                'extrainfo' => $extrainfo, ])) {
                return $extrainfo;
            }
            return $extrainfo;
        }

        // Galaxia Workflow activity
        if (!$userapi->run_activity(['activityId' => $activityId,
            'auto' => 1,
            // standard arguments for use in activity code
            'module' => $modname,
            'itemtype' => $itemtype,
            'itemid' => $objectid, ])) {
            return $extrainfo;
        }

        return $extrainfo;
    }
}
