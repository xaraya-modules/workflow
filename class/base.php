<?php

/**
 * Workflow Module Base Class
 *
 * @package modules
 * @copyright (C) copyright-placeholder
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link http://www.xaraya.com
 *
 * @subpackage Workflow Module
 * @link http://xaraya.com/index.php/release/188.html
 * @author Workflow Module Development Team
 */

namespace Xaraya\Modules\Workflow;

use Xaraya\Context\WithContextInterface;
use Xaraya\Context\WithContextTrait;
use xarObject;

class WorkflowBase extends xarObject implements WithContextInterface
{
    use WithContextTrait;

    public static function init(array $args = []) {}

    public function __construct(array $args = [], $context = null)
    {
        // @todo do something with $args
        static::init($args);
        $this->setContext($context);
    }
}
