<?php
/**
 * Workflow Module
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
/**
 * Overview displays standard Overview page
 */
function workflow_admin_overview(array $args = [], $context = null)
{
    $data = [];
    //just return to main function that displays the overview
    $data['context'] = $context;
    return xarTpl::module('workflow', 'admin', 'main', $data, 'main');
}
