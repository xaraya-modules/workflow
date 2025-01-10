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
use sys;
use BadParameterException;

sys::import('xaraya.modules.method');

/**
 * workflow admin test_manage function
 */
class TestManageMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * Test manage Symfony Workflows
     */
    public function __invoke(array $args = [])
    {
        $data = [];
        $data['context'] = $this->getContext();
        return $data;
    }
}
