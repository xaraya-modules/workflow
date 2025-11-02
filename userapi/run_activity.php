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
use Exception;

sys::import('xaraya.modules.method');

/**
 * workflow userapi run_activity function
 * @extends MethodClass<UserApi>
 */
class RunActivityMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * the run activity user API function - used by hook calls to workflow
     * @author mikespub
     * @access public
     * @see UserApi::runActivity()
     */
    public function __invoke(array $args = [])
    {
        // Security Check
        if (!$this->sec()->checkAccess('ReadWorkflow')) {
            return;
        }

        // Common setup for Galaxia environment (possibly include more than once here !)
        require_once dirname(__DIR__) . '/lib/galaxia/config.php';
        $data = [];

        // Adapted from tiki-g-run_activity.php
        include(\GALAXIA_LIBRARY . '/api.php');
        /** @var \Galaxia\Api\Instance $instance */

        // TODO: evaluate why this is here
        global $__activity_completed;
        $__activity_completed = false;

        // Determine the activity using the activityId request
        // parameter and get the activity information
        // load then the compiled version of the activity
        if (!isset($args['activityId'])) {
            throw new Exception($this->ml("No workflow activity indicated"));
        }

        $activity = \Galaxia\Api\WorkflowActivity::get($args['activityId']);
        $process = new \Galaxia\Api\Process($activity->getProcessId());

        if (!empty($args['iid']) && empty($instance->instanceId)) {
            $instance->getInstance($args['iid']);
        } elseif (!empty($args['module'])) {
            // CHECKME: if we're calling this from a hook module, we need to manually complete this
            $instance->getInstance($instance->instanceId);
            $instance->complete($args['activityId']);
        }

        $source = \GALAXIA_PROCESSES . '/' . $process->getNormalizedName() . '/compiled/' . $activity->getNormalizedName() . '.php';
        $shared = \GALAXIA_PROCESSES . '/' . $process->getNormalizedName() . '/code/shared.php';

        // Existing variables here:
        // $process, $activity, $instance (if not standalone)

        //--------------------------------------------- Execute the PHP code of the activity
        // Include the shared code
        if (file_exists($shared)) {
            include_once($shared);
        }
        if (file_exists($source)) {
            require($source);
        }

        return true;
    }
}
