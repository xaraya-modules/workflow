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

use Xaraya\Modules\Workflow\WorkflowQueue;
use Xaraya\Modules\MethodClass;
use sys;
use BadParameterException;

sys::import('xaraya.modules.method');

/**
 * workflow userapi queue function
 */
class QueueMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * the queue user API function
     * @uses \WorkflowQueue
     * @author mikespub
     * @access public
     */
    public function __invoke(array $args = [])
    {
        return new WorkflowQueue($args, $this->getContext());
    }
}
