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
use xarSec;
use xarSecurity;
use xarVar;
use xarModVars;
use xarMod;
use xarTpl;
use xarController;
use sys;
use BadParameterException;

sys::import('xaraya.modules.method');

/**
 * workflow admin updateconfig function
 * @extends MethodClass<AdminGui>
 */
class UpdateconfigMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * Update configuration
     */
    public function __invoke(array $args = [])
    {
        // Confirm authorisation code
        if (!xarSec::confirmAuthKey()) {
            return;
        }
        // Security Check
        if (!xarSecurity::check('AdminWorkflow')) {
            return;
        }

        // Get parameters
        xarVar::fetch('settings', 'isset', $settings, '', xarVar::DONT_SET);
        if (empty($settings)) {
            $settings = [];
        }
        foreach ($settings as $key => $val) {
            xarModVars::set('workflow', $key, $val);
        }

        if (!xarVar::fetch('jobs', 'isset', $jobs, [], xarVar::NOT_REQUIRED)) {
            return;
        }
        if (empty($jobs)) {
            $jobs = [];
        }
        $savejobs = [];
        foreach ($jobs as $job) {
            if (!empty($job['activity']) && !empty($job['interval'])) {
                $savejobs[] = $job;
            }
        }
        $serialjobs = serialize($savejobs);
        xarModVars::set('workflow', 'jobs', $serialjobs);

        if (xarMod::isAvailable('scheduler')) {
            if (!xarVar::fetch('interval', 'str:1', $interval, '', xarVar::NOT_REQUIRED)) {
                return;
            }
            // see if we have a scheduler job running to execute workflow activities
            $job = xarMod::apiFunc(
                'scheduler',
                'user',
                'get',
                ['module' => 'workflow',
                    'type' => 'scheduler',
                    'func' => 'activities', ]
            );
            if (empty($job) || empty($job['interval'])) {
                if (!empty($interval)) {
                    // create a scheduler job
                    xarMod::apiFunc(
                        'scheduler',
                        'admin',
                        'create',
                        ['module' => 'workflow',
                            'type' => 'scheduler',
                            'func' => 'activities',
                            'interval' => $interval, ]
                    );
                }
            } elseif (empty($interval)) {
                // delete the scheduler job
                xarMod::apiFunc(
                    'scheduler',
                    'admin',
                    'delete',
                    ['module' => 'workflow',
                        'type' => 'scheduler',
                        'func' => 'activities', ]
                );
            } elseif ($interval != $job['interval']) {
                // update the scheduler job
                xarMod::apiFunc(
                    'scheduler',
                    'admin',
                    'update',
                    ['module' => 'workflow',
                        'type' => 'scheduler',
                        'func' => 'activities',
                        'interval' => $interval, ]
                );
            }
        }

        $data['module_settings'] = xarMod::apiFunc('base', 'admin', 'getmodulesettings', ['module' => 'workflow']);
        $data['module_settings']->getItem();
        $isvalid = $data['module_settings']->checkInput();
        if (!$isvalid) {
            $data['context'] ??= $this->getContext();
            return xarTpl::module('workflow', 'admin', 'modifyconfig', $data);
        } else {
            $itemid = $data['module_settings']->updateItem();
        }

        xarController::redirect(xarController::URL('workflow', 'admin', 'modifyconfig'), null, $this->getContext());

        return true;
    }
}
