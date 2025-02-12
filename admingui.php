<?php

/**
 * @package modules\workflow
 * @category Xaraya Web Applications Framework
 * @version 2.5.7
 * @copyright see the html/credits.html file in this release
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link https://github.com/mikespub/xaraya-modules
 **/

namespace Xaraya\Modules\Workflow;

use Xaraya\Modules\AdminGuiClass;
use sys;

sys::import('xaraya.modules.admingui');
sys::import('modules.workflow.adminapi');

/**
 * Handle the workflow admin GUI
 *
 * @method mixed activities(array $args)
 * @method mixed exportProcess(array $args)
 * @method mixed graph(array $args)
 * @method mixed instance(array $args)
 * @method mixed main(array $args)
 * @method mixed modifyconfig(array $args)
 * @method mixed monitorActivities(array $args)
 * @method mixed monitorInstances(array $args)
 * @method mixed monitorProcesses(array $args)
 * @method mixed monitorWorkitems(array $args)
 * @method mixed overview(array $args)
 * @method mixed processes(array $args)
 * @method mixed roles(array $args)
 * @method mixed sharedSource(array $args)
 * @method mixed testManage(array $args)
 * @method mixed updateconfig(array $args)
 * @method mixed workitem(array $args)
 * @extends AdminGuiClass<Module>
 */
class AdminGui extends AdminGuiClass
{
    // ...
}
