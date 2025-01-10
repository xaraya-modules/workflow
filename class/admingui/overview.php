<?php

/**
 * @package modules\workflow
 * @category Xaraya Web Applications Framework
 * @version 2.5.7
 * @copyright see the html/credits.html file in this release
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link https://github.com/mikespub/xaraya-modules
**/

namespace Xaraya\Modules\Workflow\AdminGui;

use Xaraya\Modules\MethodClass;
use xarTpl;
use sys;
use BadParameterException;

sys::import('xaraya.modules.method');

/**
 * workflow admin overview function
 */
class OverviewMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * Overview displays standard Overview page
     */
    public function __invoke(array $args = [])
    {
        $data = [];
        //just return to main function that displays the overview
        $data['context'] = $this->getContext();
        return xarTpl::module('workflow', 'admin', 'main', $data, 'main');
    }
}
