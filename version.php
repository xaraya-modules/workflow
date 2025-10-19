<?php

/**
 * Workflow - based on Galaxia Workflow Engine
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

class Version
{
    /**
     * Get module version information
     *
     * @return array<string, mixed>
     */
    public function __invoke(): array
    {
        return [
            'name' => 'workflow',
            'id' => '188',
            'version' => '2.5.0',
            'displayname' => 'Workflow',
            'description' => 'Workflow Module based on the Symfony Workflow Component',
            'credits' => '',
            'help' => '',
            'changelog' => '',
            'license' => '',
            'official' => 1,
            'author' => 'mikespub',
            'contact' => 'http://www.xaraya.com/',
            'admin' => 1,
            'user' => 1,
            'class' => 'Utility',
            'category' => 'Miscellaneous',
            'namespace' => 'Xaraya\\Modules\\Workflow',
            'twigtemplates' => true,
            'dependencyinfo'
             => [
                 0
                  => [
                      'name' => 'Xaraya Core',
                      'version_ge' => '2.4.1',
                  ],
             ],
        ];
    }
}
