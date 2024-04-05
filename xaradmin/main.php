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
 * the main administration function
 *
 * @author mikespub
 * @access public
 * @return bool|void true on success or void on falure
 */
function workflow_admin_main(array $args = [], $context = null)
{
    // Security Check
    if (!xarSecurity::check('AdminWorkflow')) {
        return;
    }

    xarController::redirect(xarController::URL('workflow', 'admin', 'processes'), null, $context);
    // success
    return true;
}
