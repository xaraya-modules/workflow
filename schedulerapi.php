<?php

/**
 * @package modules\workflow
 * @category Xaraya Web Applications Framework
 * @version 2.6.2
 * @copyright see the html/credits.html file in this release
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link https://github.com/mikespub/xaraya-modules
**/

namespace Xaraya\Modules\Workflow;

use Xaraya\Modules\UserApiClass;

/**
 * Handle the workflow scheduler API
 * @method mixed activities(array $args = []) run all scheduled workflow activities (executed by the scheduler module)
 * @method mixed transitions(array $args = []) run all scheduled workflow transitions (executed by the scheduler module)
 * @extends UserApiClass<Module>
 */
class SchedulerApi extends UserApiClass
{
    public function configure()
    {
        $this->setModType('scheduler');
        // don't call xarMod:apiLoad() for workflow scheduler API
    }
}
