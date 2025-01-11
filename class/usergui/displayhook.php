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
use sys;
use BadParameterException;

sys::import('xaraya.modules.method');

/**
 * workflow user displayhook function
 * @extends MethodClass<UserGui>
 */
class DisplayhookMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * Workflow Module
     * @package modules
     * @copyright (C) copyright-placeholder
     * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
     * @link http://www.xaraya.com
     * @subpackage Workflow Module
     * @link http://xaraya.com/index.php/release/188.html
     * @author Marc Lutolf <mfl@netspan.ch>
     */
    public function __invoke(array $args = [])
    {
        //return var_export($args, true);
        extract($args);

        // everything is already validated in HookSubject, except possible empty objectid/itemid for create/display
        $modname = $extrainfo['module'];
        $itemtype = $extrainfo['itemtype'];
        $itemid = $extrainfo['itemid'];
        $modid = $extrainfo['module_id'];

        // Symfony Workflow transition
        //return 'Workflow user displayhook was here for Symfony Workflow transition...';
        // Galaxia Workflow activity
        //return 'Workflow user displayhook was here for Galaxia Workflow activity...';
        return '';
    }
}
