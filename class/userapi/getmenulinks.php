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
use xarSecurity;
use xarController;
use sys;
use BadParameterException;

sys::import('xaraya.modules.method');

/**
 * workflow userapi getmenulinks function
 * @extends MethodClass<UserApi>
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
        if (xarSecurity::check('ReadWorkflow', 0)) {
            $menulinks[] = ['url'   => xarController::URL(
                'workflow',
                'user',
                'display'
            ),
                'title' => xarML('Links to all the available interactive processes'),
                'label' => xarML('Runnable Activities'), ];
            $menulinks[] = ['url'   => xarController::URL(
                'workflow',
                'user',
                'processes'
            ),
                'title' => xarML('View your workflow processes'),
                'label' => xarML('Processes'), ];
            $menulinks[] = ['url'   => xarController::URL(
                'workflow',
                'user',
                'activities'
            ),
                'title' => xarML('View your workflow activities'),
                'label' => xarML('Activities'), ];
            $menulinks[] = ['url'   => xarController::URL(
                'workflow',
                'user',
                'instances'
            ),
                'title' => xarML('View your workflow instances'),
                'label' => xarML('Instances'), ];
            $menulinks[] = ['url'   => xarController::URL(
                'workflow',
                'user',
                'test'
            ),
                'title' => xarML('View your workflow test'),
                'label' => xarML('Test New Workflows'), ];
        }

        return $menulinks;
    }
}
