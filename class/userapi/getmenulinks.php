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
        if ($this->checkAccess('ReadWorkflow', 0)) {
            $menulinks[] = ['url'   => $this->getUrl('user', 'display'),
                'title' => $this->translate('Links to all the available interactive processes'),
                'label' => $this->translate('Runnable Activities'), ];
            $menulinks[] = ['url'   => $this->getUrl('user', 'processes'),
                'title' => $this->translate('View your workflow processes'),
                'label' => $this->translate('Processes'), ];
            $menulinks[] = ['url'   => $this->getUrl('user', 'activities'),
                'title' => $this->translate('View your workflow activities'),
                'label' => $this->translate('Activities'), ];
            $menulinks[] = ['url'   => $this->getUrl('user', 'instances'),
                'title' => $this->translate('View your workflow instances'),
                'label' => $this->translate('Instances'), ];
            $menulinks[] = ['url'   => $this->getUrl('user', 'test'),
                'title' => $this->translate('View your workflow test'),
                'label' => $this->translate('Test New Workflows'), ];
        }

        return $menulinks;
    }
}
