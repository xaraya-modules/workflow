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
use Xaraya\Modules\Workflow\WorkflowEventSubscriber;
use Xaraya\Modules\MethodClass;
use sys;

/**
 * workflow userapi eventsubscriber function
 * @extends MethodClass<UserApi>
 */
class EventsubscriberMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * the eventsubscriber user API function
     * @uses \sys::autoload()
     * @uses WorkflowEventSubscriber
     * @author mikespub
     * @access public
     * @see UserApi::eventsubscriber()
     */
    public function __invoke(array $args = [])
    {
        sys::autoload();
        return new WorkflowEventSubscriber($args, $this->getContext());
    }
}
