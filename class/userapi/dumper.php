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
use Xaraya\Modules\Workflow\WorkflowDumper;
use Xaraya\Modules\MethodClass;
use sys;
use BadParameterException;

sys::import('xaraya.modules.method');

/**
 * workflow userapi dumper function
 * @extends MethodClass<UserApi>
 */
class DumperMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * the dumper user API function
     * @uses \sys::autoload()
     * @uses WorkflowDumper
     * @author mikespub
     * @access public
     * @see UserApi::dumper()
     */
    public function __invoke(array $args = [])
    {
        sys::autoload();
        // @todo do something with $args and $this->getContext()
        return new WorkflowDumper();
    }
}
