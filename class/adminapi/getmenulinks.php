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
use xarSecurity;
use xarController;
use sys;
use BadParameterException;

sys::import('xaraya.modules.method');

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
     */
    public function __invoke(array $args = [])
    {
        $menulinks = [];

        // Security Check
        if ($this->checkAccess('AdminWorkflow', 0)) {
            $menulinks[] = ['url'   => $this->getUrl('admin', 'overview'),
                'title' => $this->translate('Overview of the Workflow module'),
                'label' => $this->translate('Overview'), ];
            $menulinks[] = ['url'   => $this->getUrl('admin', 'monitor_processes'),
                'title' => $this->translate('Monitor the workflow processes'),
                'label' => $this->translate('Monitoring'), ];
            $menulinks[] = ['url'   => $this->getUrl('admin', 'processes'),
                'title' => $this->translate('Edit the workflow processes'),
                'label' => $this->translate('Manage Processes'), ];
            $menulinks[] = ['url'   => $this->getUrl('admin', 'test_manage'),
                'title' => $this->translate('Manage New Workflows'),
                'label' => $this->translate('New Workflows'), ];
            $menulinks[] = ['url'   => $this->getUrl('admin', 'modifyconfig'),
                'title' => $this->translate('Modify the workflow configuration'),
                'label' => $this->translate('Modify Config'), ];
        }

        return $menulinks;
    }
}
