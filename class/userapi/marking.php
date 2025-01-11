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

use Xaraya\Modules\Workflow\WorkflowMarking;
use Xaraya\Modules\MethodClass;
use sys;
use BadParameterException;

sys::import('xaraya.modules.method');

/**
 * workflow userapi marking function
 */
class MarkingMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * the marking user API function
     * @uses \WorkflowMarking
     * @author mikespub
     * @access public
     */
    public function __invoke(array $args = [])
    {
        return new WorkflowMarking($args, $this->getContext());
    }
}
