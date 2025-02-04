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
use Xaraya\Modules\Workflow\WorkflowProcess;
use Xaraya\Modules\MethodClass;
use sys;
use BadParameterException;

sys::import('xaraya.modules.method');

/**
 * workflow userapi process function
 * @extends MethodClass<UserApi>
 */
class ProcessMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * the process user API function
     * @uses \sys::autoload()
     * @uses WorkflowProcess
     * @author mikespub
     * @access public
     * @see UserApi::process()
     */
    public function __invoke(array $args = [])
    {
        sys::autoload();
        return new WorkflowProcess($args, $this->getContext());
    }
}
