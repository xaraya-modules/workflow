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
use Xaraya\Modules\Workflow\WorkflowSubject;
use Xaraya\Modules\MethodClass;
use sys;
use BadParameterException;

sys::import('xaraya.modules.method');

/**
 * workflow userapi subject function
 * @extends MethodClass<UserApi>
 */
class SubjectMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * the subject user API function
     * @uses WorkflowSubject
     * @author mikespub
     * @access public
     */
    public function __invoke(array $args = [])
    {
        $args['object'] ??= 'dummy';
        $args['itemid'] ??= 0;
        $subject = new WorkflowSubject($args['object'], (int) $args['itemid']);
        $subject->setContext($this->getContext());

        return $subject;
    }
}
