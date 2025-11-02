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
use Xaraya\Modules\MethodClass;
use sys;

sys::import('xaraya.modules.method');

/**
 * workflow userapi getinstance function
 * @extends MethodClass<UserApi>
 */
class GetinstanceMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * Addition to the workflow module when there is a need
     * to retrieve the actual instance rather than just an
     * array of values. This can be used in conjunction with
     * the "findinstances" api.
     * @author Mike Dunn submitted by Court Shrock
     * @access public
     * @param mixed $instaceId (required)
     * @return \Galaxia\Api\Instance|void workflow Instance
     * @see UserApi::getinstance()
     */
    public function __invoke(array $args = [])
    {
        require_once dirname(__DIR__) . '/lib/galaxia/config.php';

        //make sure this user an access this instance
        if (!$this->sec()->checkAccess('ReadWorkflow')) {
            return;
        }

        extract($args);

        //if not instance is set send this back we cannon continue
        if (!isset($instanceId)) {
            return;
        }

        //check to see if this hasn't alredy been done
        if (!function_exists("getInstance")) {
            include_once(\GALAXIA_LIBRARY . '/api.php');
        }

        $inst = new \Galaxia\Api\Instance();
        $inst->getInstance($instanceId);

        return $inst;
    }
}
