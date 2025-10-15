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
use Xaraya\Modules\Workflow\WorkflowTracker;
use Xaraya\Modules\MethodClass;
use sys;

sys::import('xaraya.modules.method');

/**
 * workflow userapi tracker function
 * @extends MethodClass<UserApi>
 */
class TrackerMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * the tracker user API function
     * @uses WorkflowTracker
     * @author mikespub
     * @access public
     * @see UserApi::tracker()
     */
    public function __invoke(array $args = [])
    {
        return new WorkflowTracker($args, $this->getContext());
    }
}
