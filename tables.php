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
 * @author mikespub
 */

namespace Xaraya\Modules\Workflow;

use xarDB;

class Tables
{
    /**
     * Return workflow table names to xaraya
     *
     * This function is called internally by the core whenever the module is
     * loaded. It is loaded by xarMod::loadDbInfo().
     *
     * @access private
     * @return array
     */
    public function __invoke(?string $prefix = null)
    {
        // Initialise table array
        $xarTables = [];

        $prefix ??= xarDB::getPrefix();

        // Get the name for the workflow tables.
        $xarTables['workflow_activities'] = $prefix . '_workflow_activities';
        $xarTables['workflow_activity_roles'] = $prefix . '_workflow_activity_roles';
        $xarTables['workflow_instance_activities'] = $prefix . '_workflow_instance_activities';
        $xarTables['workflow_instance_comments'] = $prefix . '_workflow_instance_comments';
        $xarTables['workflow_instances'] = $prefix . '_workflow_instances';
        $xarTables['workflow_processes'] = $prefix . '_workflow_processes';
        $xarTables['workflow_roles'] = $prefix . '_workflow_roles';
        $xarTables['workflow_transitions'] = $prefix . '_workflow_transitions';
        $xarTables['workflow_user_roles'] = $prefix . '_workflow_user_roles';
        $xarTables['workflow_workitems'] = $prefix . '_workflow_workitems';

        // Return the table information
        return $xarTables;
    }
}
