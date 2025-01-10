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

use Xaraya\Modules\MethodClass;
use xarSecurity;
use xarController;
use sys;
use BadParameterException;

sys::import('xaraya.modules.method');

/**
 * workflow adminapi getmenulinks function
 */
class GetmenulinksMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * utility function pass individual menu items to the main menu
     * @author the Workflow module development team
     * @return array containing the menulinks for the main menu items.
     */
    public function __invoke(array $args = [])
    {
        $menulinks = [];

        // Security Check
        if (xarSecurity::check('AdminWorkflow', 0)) {
            $menulinks[] = ['url'   => xarController::URL(
                'workflow',
                'admin',
                'overview'
            ),
                'title' => xarML('Overview of the Workflow module'),
                'label' => xarML('Overview'), ];
            $menulinks[] = ['url'   => xarController::URL(
                'workflow',
                'admin',
                'monitor_processes'
            ),
                'title' => xarML('Monitor the workflow processes'),
                'label' => xarML('Monitoring'), ];
            $menulinks[] = ['url'   => xarController::URL(
                'workflow',
                'admin',
                'processes'
            ),
                'title' => xarML('Edit the workflow processes'),
                'label' => xarML('Manage Processes'), ];
            $menulinks[] = ['url'   => xarController::URL(
                'workflow',
                'admin',
                'test_manage'
            ),
                'title' => xarML('Manage New Workflows'),
                'label' => xarML('New Workflows'), ];
            $menulinks[] = ['url'   => xarController::URL(
                'workflow',
                'admin',
                'modifyconfig'
            ),
                'title' => xarML('Modify the workflow configuration'),
                'label' => xarML('Modify Config'), ];
        }

        return $menulinks;
    }
}
