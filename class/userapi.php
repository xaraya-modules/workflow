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

use Xaraya\Modules\UserApiClass;
use sys;

sys::import('xaraya.modules.userapi');

/**
 * Handle the workflow user API
 *
 * @method mixed config(array $args)
 * @method mixed definition(array $args)
 * @method mixed dumper(array $args)
 * @method mixed eventsubscriber(array $args)
 * @method mixed findinstances(array $args)
 * @method mixed getactivityid(array $args)
 * @method mixed getconfig(array $args)
 * @method mixed getinstance(array $args)
 * @method mixed getitemlinks(array $args)
 * @method mixed getitemtypes(array $args)
 * @method mixed getmenulinks(array $args)
 * @method mixed handlers(array $args)
 * @method mixed history(array $args)
 * @method mixed logger(array $args)
 * @method mixed marking(array $args)
 * @method mixed mermaid(array $args)
 * @method mixed process(array $args)
 * @method mixed queue(array $args)
 * @method mixed registry(array $args)
 * @method mixed runActivity(array $args)
 * @method mixed runTransition(array $args)
 * @method mixed showactions(array $args)
 * @method mixed showactivity(array $args)
 * @method mixed showinstances(array $args)
 * @method mixed showstatus(array $args)
 * @method mixed subject(array $args)
 * @method mixed timetodhms(array $args)
 * @method mixed tracker(array $args)
 * @method mixed transition(array $args)
 * @extends UserApiClass<Module>
 */
class UserApi extends UserApiClass
{
    // ...
}
