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

use Xaraya\Modules\UserApiInterface;

/**
 * Trait to handle other api/gui functions
 */
trait OtherApiTrait
{
    /**
     * Get module scheduler API class for this module
     */
    public function schedulerapi(): ?UserApiInterface
    {
        $component = $this->getModule()->getComponent('SchedulerApi');
        assert($component instanceof UserApiInterface);
        return $component;
    }
}
