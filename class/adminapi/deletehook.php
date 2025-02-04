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
use xarModVars;
use xarMod;
use sys;
use BadParameterException;

sys::import('xaraya.modules.method');

/**
 * workflow adminapi deletehook function
 * @extends MethodClass<AdminApi>
 */
class DeletehookMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * start a delete activity for a module item - hook for ('item','delete','API')
     * @param array<mixed> $args
     * @var mixed $objectid ID of the object
     * @var mixed $extrainfo extra information
     * @return bool true on success, false on failure
     * @see AdminApi::deletehook()
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
        if (empty($itemid)) {
            $msg = 'Invalid #(1) for #(2) function #(3)() in module #(4)';
            $vars = ['item id', 'admin', 'deletehook', 'workflow'];
            throw new BadParameterException($vars, $msg);
        }

        // see if we need to start some workflow activity here
        if (!empty($itemtype)) {
            $activityId = $this->mod()->getVar("$modname.$itemtype.delete");
        }
        if (empty($activityId)) {
            $activityId = $this->mod()->getVar("$modname.delete");
        }
        if (empty($activityId)) {
            $activityId = $this->mod()->getVar('default.delete');
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
                'hooktype' => 'ItemDelete',
                'module' => $modname,
                'itemtype' => $itemtype,
                'itemid' => $itemid,
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
            'itemid' => $itemid, ])) {
            return $extrainfo;
        }

        // Return the extra info
        return $extrainfo;
    }
}
