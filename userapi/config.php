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
use Xaraya\Modules\Workflow\WorkflowConfig;
use Xaraya\Modules\MethodClass;
use sys;
use BadParameterException;

sys::import('xaraya.modules.method');

/**
 * workflow userapi config function
 * @extends MethodClass<UserApi>
 */
class ConfigMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * the config user API function
     * @uses WorkflowConfig
     * @author mikespub
     * @access public
     * @see UserApi::config()
     */
    public function __invoke(array $args = [])
    {
        return new WorkflowConfig($args, $this->getContext());
    }
}
