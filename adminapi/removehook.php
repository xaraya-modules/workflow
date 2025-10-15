<?php

/**
 * @package modules\workflow
 * @category Xaraya Web Applications Framework
 * @version 2.5.7
 * @copyright see the html/credits.html file in this release
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link https://github.com/mikespub/xaraya-modules
**/

namespace Xaraya\Modules\Workflow\AdminApi;

use Xaraya\Modules\Workflow\AdminApi;
use Xaraya\Modules\MethodClass;
use sys;
use BadParameterException;

sys::import('xaraya.modules.method');

/**
 * workflow adminapi removehook function
 * @extends MethodClass<AdminApi>
 */
class RemovehookMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * delete all entries for a module - hook for ('module','remove','API')
     * @param array<mixed> $args
     * @var mixed $objectid ID of the object (must be the module name here !!)
     * @var mixed $extrainfo extra information
     * @return bool true on success, false on failure
     * @see AdminApi::removehook()
     */
    public function __invoke(array $args = [])
    {
        extract($args);

        if (!isset($extrainfo)) {
            $extrainfo = [];
        }

        // When called via hooks, we should get the real module name from objectid
        // here, because the current module is probably going to be 'modules' !!!
        if (!isset($objectid) || !is_string($objectid)) {
            $msg = 'Invalid #(1) for #(2) function #(3)() in module #(4)';
            $vars = ['object ID (= module name)', 'admin', 'removehook', 'workflow'];
            throw new BadParameterException($vars, $msg);
        }

        $modid = $this->mod()->getRegID($objectid);
        if (empty($modid)) {
            $msg = 'Invalid #(1) for #(2) function #(3)() in module #(4)';
            $vars = ['module ID', 'admin', 'removehook', 'workflow'];
            throw new BadParameterException($vars, $msg);
        }

        // TODO: do we delete/close all instances for this module ?

        // Return the extra info
        return $extrainfo;
    }
}
