<?php

/**
 * @package modules\workflow
 * @category Xaraya Web Applications Framework
 * @version 2.5.7
 * @copyright see the html/credits.html file in this release
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link https://github.com/mikespub/xaraya-modules
**/

namespace Xaraya\Modules\Workflow\UserGui;


use Xaraya\Modules\Workflow\UserGui;
use Xaraya\Modules\MethodClass;
use xarSecurity;
use sys;
use BadParameterException;

sys::import('xaraya.modules.method');

/**
 * workflow user main function
 * @extends MethodClass<UserGui>
 */
class MainMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * the main user function
     * @author mikespub
     * @access public
     * @return array|void empty
     */
    public function __invoke(array $args = [])
    {
        // Security Check
        if (!$this->checkAccess('ReadWorkflow')) {
            return;
        }

        return [];
    }
}
