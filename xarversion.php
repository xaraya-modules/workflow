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
$modversion['name']           = 'workflow';
$modversion['id']             = '188';
$modversion['version']        = '2.5.0';
$modversion['displayname']    = xarMLS::translate('Workflow');
$modversion['description']    = 'Workflow Module based on the Symfony Workflow Component';
$modversion['credits']        = '';
$modversion['help']           = '';
$modversion['changelog']      = '';
$modversion['license']        = '';
$modversion['official']       = 1;
$modversion['author']         = 'mikespub';
$modversion['contact']        = 'http://www.xaraya.com/';
$modversion['admin']          = 1;
$modversion['user']           = 1;
$modversion['class']          = 'Utility';
$modversion['category']       = 'Miscellaneous';
$modversion['namespace']      = 'Xaraya\Modules\Workflow';
$modversion['twigtemplates']  = true;
$modversion['dependencyinfo'] = [
    0 => [
        'name' => 'Xaraya Core',
        'version_ge' => '2.4.1',
    ],
];
