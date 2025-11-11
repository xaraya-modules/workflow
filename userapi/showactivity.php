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
use xarTpl;

/**
 * workflow userapi showactivity function
 * @extends MethodClass<UserApi>
 */
class ShowactivityMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * show the result of a workflow activity (called via <xar:workflow-activity tag)
     * @author mikespub
     * @access public
     * @see UserApi::showactivity()
     */
    public function __invoke(array $args = [])
    {
        // Security Check
        if (!$this->sec()->checkAccess('ReadWorkflow', 0)) {
            return '';
        }

        // Common setup for Galaxia environment
        require_once dirname(__DIR__) . '/lib/galaxia/config.php';
        $tplData = [];

        include(\GALAXIA_LIBRARY . '/api.php');
        /** @var \Galaxia\Api\Instance $instance */

        if (empty($args['activityId'])) {
            return $this->ml("No activity found");
        }

        $activity = \Galaxia\Api\WorkflowActivity::get($args['activityId']);
        $process = new \Galaxia\Api\Process($activity->getProcessId());

        if (empty($user)) {
            $user = $this->user()->getId();
        }
        if (!empty($args['instanceId'])) {
            $instance->getInstance($args['instanceId']);
            $instance->setActivityUser($activity->getActivityId(), $user);
        }

        // Get user roles

        // Get activity roles
        $act_roles = $activity->getRoles();
        $user_roles = $activity->getUserRoles($user);

        // Only check roles if this is an interactive
        // activity
        if ($activity->isInteractive()) {
            // TODO: revisit this when roles/users is clearer
            $canrun = false;
            foreach ($act_roles as $candidate) {
                if (in_array($candidate["roleId"], $user_roles)) {
                    $canrun = true;
                }
            }
            if (!$canrun) {
                return $this->ml("You can't execute this activity");
            }
        }

        $act_role_names = $activity->getActivityRoleNames($user);

        // FIXME: what's this for ?
        foreach ($act_role_names as $role) {
            $name = 'tiki-role-' . $role['name'];

            if (in_array($role['roleId'], $user_roles)) {
                $tplData[$name] = 'y';
                $$name = 'y';
            } else {
                $tplData[$name] = 'n';
                $$name = 'n';
            }
        }

        $source = \GALAXIA_PROCESSES . '/' . $process->getNormalizedName() . '/compiled/' . $activity->getNormalizedName() . '.php';
        $shared = \GALAXIA_PROCESSES . '/' . $process->getNormalizedName() . '/code/shared.php';

        // Existing variables here:
        // $process, $activity, $instance (if not standalone)

        // Include the shared code
        include_once($shared);

        // Now do whatever you have to do in the activity
        include_once($source);

        // This goes to the end part of all activities
        // If this activity is interactive then we have to display the template

        $tplData['procname'] =  $process->getName();
        $tplData['procversion'] =  $process->getVersion();
        $tplData['actname'] =  $activity->getName();
        $tplData['actid'] = $activity->getActivityId();

        // Put the current activity id in a template variable
        $tplData['activityId'] = $activity->getActivityId();

        // Put the current instance id in a template variable
        $tplData['iid'] = $instance->getInstanceId();

        // URL to return to if some action is taken
        $tplData['return_url'] = $this->ctl()->getCurrentURL();

        if ($activity->isInteractive()) {
            $template = $activity->getNormalizedName() . '.xt';
            // not very clean way, but it works :)
            $output = xarTpl::file(\GALAXIA_PROCESSES . '/' . $process->getNormalizedName() . '/code/templates/' . $template, $tplData);
            return $output;
        } else {
            $instance->getInstance($instance->instanceId);
            $instance->complete($activity->getActivityId());
            return '';
        }
    }
}
