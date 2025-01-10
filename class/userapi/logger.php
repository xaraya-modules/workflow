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

use Xaraya\Modules\MethodClass;
use sys;
use BadParameterException;

sys::import('xaraya.modules.method');

/**
 * workflow userapi logger function
 */
class LoggerMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * the logger user API function
     * @uses \sys::autoload()
     * @uses \WorkflowLogger
     * @author mikespub
     * @access public
     */
    public function __invoke(array $args = [])
    {
        sys::autoload();
        // @todo do something with $args and $this->getContext()
        $args['prefix'] ??= '';
        return new WorkflowLogger($args['prefix']);
    }
}
