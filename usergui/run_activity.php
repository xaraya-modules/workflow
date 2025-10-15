<?php

/**
 * @package modules\workflow
 * @category Xaraya Web Applications Framework
 * @version 2.5.7
 * @copyright see the html/credits.html file in this release
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link https://github.com/mikespub/xaraya-modules
**/

namespace Xaraya\Modules\Workflow\UserGui;

use Xaraya\Modules\Workflow\UserGui;
use Xaraya\Modules\MethodClass;
use xarTpl;
use xarModHooks;
use sys;

sys::import('xaraya.modules.method');

/**
 * workflow user run_activity function
 * @extends MethodClass<UserGui>
 */
class RunActivityMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * the run activity user function
     * @author mikespub
     * @access public
     * @see UserGui::runActivity()
     */
    public function __invoke(array $args = [])
    {
        $this->log()->debug("Running activity");
        // Security Check
        // CHECKME: what if an activity is Auto? (probably nothing different)
        if (!$this->sec()->checkAccess('ReadWorkflow')) {
            return;
        }

        // Common setup for Galaxia environment
        sys::import('modules.workflow.lib.galaxia.config');
        $data = [];
        // global $user variable used by instance
        global $user;
        $user = $this->user()->getId();
        //--------------------------------------------- Load the instance class
        include(GALAXIA_LIBRARY . '/api.php');
        /** @var \Galaxia\Api\Instance $instance */

        // TODO: evaluate why this is here
        global $__activity_completed;
        global $__comments;
        $__activity_completed = false;

        // Determine the activity using the activityId request
        // parameter and get the activity information
        // load then the compiled version of the activity
        if (!isset($_REQUEST['activityId'])) {
            $data['msg'] =  $this->ml("No workflow activity indicated");
            $data['context'] ??= $this->getContext();
            return $this->mod()->template('errors', $data);
        }

        $activity = \Galaxia\Api\WorkflowActivity::get($_REQUEST['activityId']);
        if (empty($activity)) {
            $data['msg'] = $this->ml("Invalid workflow activity specified");
            $data['context'] ??= $this->getContext();
            return $this->mod()->template('errors', $data);
        }
        $process = new \Galaxia\Api\Process($activity->getProcessId());
        $instance->pId = $activity->getProcessId();

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
                var_dump($act_roles);
                $data['msg'] =  $this->ml("You can't execute this activity");
                $data['context'] ??= $this->getContext();
                return $this->mod()->template('errors', $data);
            }
        }

        $act_role_names = $activity->getActivityRoleNames($user);

        // FIXME: what's this for ?
        foreach ($act_role_names as $role) {
            $name = 'tiki-role-' . $role['name'];

            if (in_array($role['roleId'], $user_roles)) {
                $data[$name] = 'y';
                $$name = 'y';
            } else {
                $data[$name] = 'n';
                $$name = 'n';
            }
        }

        $source = GALAXIA_PROCESSES . '/' . $process->getNormalizedName() . '/compiled/' . $activity->getNormalizedName() . '.php';
        $shared = GALAXIA_PROCESSES . '/' . $process->getNormalizedName() . '/code/shared.php';

        // Existing variables here:
        // $process, $activity, $instance (if not standalone)
        //--------------------------------------------- Execute the PHP code of the activity

        // Include the shared code
        if (file_exists($shared)) {
            include_once($shared);
        }

        // Now do whatever you have to do in the activity
        // cls - this needs to be included each time, otherwise you can't run more than 1 activity per request
        if (file_exists($source)) {
            require($source);
        }

        //---------------------------------------------
        // Process comments
        if (isset($_REQUEST['__removecomment'])) {
            $__comment = $instance->get_instance_comment($_REQUEST['__removecomment']);

            if ($__comment['user'] == $user or $this->sec()->checkAccess('AdminWorkflow', 0)) {
                $instance->remove_instance_comment($_REQUEST['__removecomment']);
            }
        }

        $data['__comments'] = &$__comments;

        if (!isset($_REQUEST['__cid'])) {
            $_REQUEST['__cid'] = 0;
        }

        if (isset($_REQUEST['__post'])) {
            $instance->replace_instance_comment(
                $_REQUEST['__cid'],
                $activity->getActivityId(),
                $activity->getName(),
                $user,
                $_REQUEST['__title'],
                $_REQUEST['__comment']
            );
        }

        $__comments = $instance->get_instance_comments();

        // This goes to the end part of all activities
        // If this activity is interactive then we have to display the template

        $data['procname'] =  $process->getName();
        $data['procversion'] =  $process->getVersion();
        $data['actname'] =  $activity->getName();
        $data['actid'] = $activity->getActivityId();

        // Put the current activity id in a template variable
        $data['activityId'] = $activity->getActivityId();

        // Put the current instance id in a template variable
        $data['iid'] = $instance->getInstanceId();

        // URL to return to if some action is taken - use htmlspecialchars() here
        if (!empty($_REQUEST['return_url'])) {
            $data['return_url'] = htmlspecialchars($_REQUEST['return_url']);
        } else {
            $data['return_url'] = '';
        }

        //--------------------------------------------- Redirect the process to the next activity (or not)

        if (!isset($_REQUEST['auto']) && $activity->isInteractive() && $__activity_completed) {
            //--------------------------------------------- This activity is completed
            if (!empty($_REQUEST['return_url'])) {
                //--------------------------------------------- We have a return_url; send us there

                $this->ctl()->redirect($_REQUEST['return_url']);
            } elseif (empty($instance->instanceId)) {
                //--------------------------------------------- No return_url or instance given. go to the activities page

                $this->ctl()->redirect($this->mod()->getURL('user', 'activities'));
            } else {
                //--------------------------------------------- No return_url, but an instance given. go to the instances page

                $this->ctl()->redirect($this->mod()->getURL('user', 'display'));
            }
            return true;
            //    } elseif (!isset($_REQUEST['auto']) && $activity->isInteractive() && $activity->getType() == 'standalone' && !empty($_REQUEST['return_url'])) {
        } elseif (!isset($_REQUEST['auto']) && $activity->getType() == 'standalone' && !empty($_REQUEST['return_url'])) {
            //---------------------------------------------  Case of a completed standalone activity <-- REVIEW THIS

            $this->ctl()->redirect($_REQUEST['return_url']);
            return true;
        } else {
            //        if (!isset($_REQUEST['auto']) && $activity->isInteractive()) {

            //--------------------------------------------- This activity is not completed

            if ((!isset($_REQUEST['auto']) || !$_REQUEST['auto']) && $activity->isInteractive()) {
                //--------------------------------------------- This activity is interactive and not autorouted

                // This activity is interactive and not autorouted. Run it and then halt
                //$section = 'workflow';
                //include_once ('tiki-section_options.php');
                $template = $activity->getNormalizedName() . '.xt';
                //            $data['mid'] =  $process->getNormalizedName(). '/' . $template;
                // not very clean way, but it works :)
                $data['mid'] = '';

                if ($activity->isInteractive()) {
                    $output = xarTpl::file(GALAXIA_PROCESSES . '/' . $process->getNormalizedName() . '/code/templates/' . $template, $data);
                    $data['mid'] = $output;
                }

                $template = 'running';

                // call display hooks if we have an instance
                if (!empty($instance->instanceId)) {
                    // get object properties for this instance - that's a bit much :)
                    //$item = get_object_vars($instance);
                    $props = ['pId','instanceId','properties','owner','status','started','ended','nextActivity','nextUser','workitems'];
                    $item = [];
                    foreach ($props as $prop) {
                        $item[$prop] = $instance->$prop;
                    }
                    $item['module'] = 'workflow';
                    $item['itemtype'] = $activity->getProcessId();
                    $item['itemid'] = $instance->getInstanceId();
                    $item['returnurl'] = $this->mod()->getURL(
                        'user',
                        'run_activity',
                        ['activityId' => $activity->getActivityId(),
                            'iid' => $instance->getInstanceId(), ]
                    );
                    $data['hooks'] = xarModHooks::call('item', 'display', $instance->instanceId, $item);
                }

                // If we are not testing, then display the output in its own page
                // Otherwise display it as part of this page
                if (!$this->user()->isSiteAdmin()) {
                    return $output;
                }
            } elseif (isset($_REQUEST['auto']) && $activity->isInteractive()) {
                //--------------------------------------------- This activity is interactive and autorouted

                // This activity is interactive and autorouted. Run it and then set up for the next activity
                $template = 'completed';
            } else {
                //--------------------------------------------- This activity is not interactive and not autorouted

                // This activity is not interactive and autorouted. Send it on
                $instance->complete();
                $template = 'completed';
            }
        }
        $data['context'] ??= $this->getContext();
        return $this->mod()->template('activity', $data, $template);
    }
}
