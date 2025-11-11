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
use Xaraya\Modules\MethodClass;

/**
 * workflow adminapi getmenulinks function
 * @extends MethodClass<AdminApi>
 */
class GetmenulinksMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * utility function pass individual menu items to the main menu
     * @author the Workflow module development team
     * @return array containing the menulinks for the main menu items.
     * @see AdminApi::getmenulinks()
     */
    public function __invoke(array $args = [])
    {
        $menulinks = [];

        // Security Check
        if ($this->sec()->checkAccess('AdminWorkflow', 0)) {
            $menulinks[] = ['url'   => $this->mod()->getURL('admin', 'overview'),
                'title' => $this->ml('Overview of the Workflow module'),
                'label' => $this->ml('Overview'), ];
            $menulinks[] = ['url'   => $this->mod()->getURL('admin', 'monitor_processes'),
                'title' => $this->ml('Monitor the workflow processes'),
                'label' => $this->ml('Monitoring'), ];
            $menulinks[] = ['url'   => $this->mod()->getURL('admin', 'processes'),
                'title' => $this->ml('Edit the workflow processes'),
                'label' => $this->ml('Manage Processes'), ];
            $menulinks[] = ['url'   => $this->mod()->getURL('admin', 'test_manage'),
                'title' => $this->ml('Manage New Workflows'),
                'label' => $this->ml('New Workflows'), ];
            $menulinks[] = ['url'   => $this->mod()->getURL('admin', 'modifyconfig'),
                'title' => $this->ml('Modify the workflow configuration'),
                'label' => $this->ml('Modify Config'), ];
        }

        return $menulinks;
    }
}
