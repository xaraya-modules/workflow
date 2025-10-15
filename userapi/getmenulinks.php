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
     * @see UserApi::getmenulinks()
     */
    public function __invoke(array $args = [])
    {
        $menulinks = [];

        // Security Check
        if ($this->sec()->checkAccess('ReadWorkflow', 0)) {
            $menulinks[] = ['url'   => $this->mod()->getURL('user', 'display'),
                'title' => $this->ml('Links to all the available interactive processes'),
                'label' => $this->ml('Runnable Activities'), ];
            $menulinks[] = ['url'   => $this->mod()->getURL('user', 'processes'),
                'title' => $this->ml('View your workflow processes'),
                'label' => $this->ml('Processes'), ];
            $menulinks[] = ['url'   => $this->mod()->getURL('user', 'activities'),
                'title' => $this->ml('View your workflow activities'),
                'label' => $this->ml('Activities'), ];
            $menulinks[] = ['url'   => $this->mod()->getURL('user', 'instances'),
                'title' => $this->ml('View your workflow instances'),
                'label' => $this->ml('Instances'), ];
            $menulinks[] = ['url'   => $this->mod()->getURL('user', 'test'),
                'title' => $this->ml('View your workflow test'),
                'label' => $this->ml('Test New Workflows'), ];
        }

        return $menulinks;
    }
}
