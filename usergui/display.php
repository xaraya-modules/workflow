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
use Query;
use sys;

sys::import('xaraya.modules.method');

/**
 * workflow user display function
 * @extends MethodClass<UserGui>
 */
class DisplayMethod extends MethodClass
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
     * @see UserGui::display()
     */
    public function __invoke(array $args = [])
    {
        if (!$this->sec()->checkAccess('ReadWorkflow')) {
            return;
        }
        $this->tpl()->setPageTitle('Display Activities');

        // Get all the activities
        sys::import('modules.dynamicdata.class.objects.factory');
        $activities = $this->data()->getObjectList(['name' => 'workflow_activities']);
        //    $where = "type = 'start'";
        $activities->getItems();

        // For each activity get the roles and add them to the activity
        sys::import('xaraya.structures.query');
        $tables = $this->db()->getTables();
        $q = new Query('SELECT', $tables['workflow_activity_roles']);
        foreach ($activities->items as $key => $item) {
            $q->eq('activityId', $item['id']);
            $q->run();
            $result = $q->output();
            $q->clearconditions();
            if (!empty($result)) {
                $roles = [];
                foreach ($result as $row) {
                    $roles[] = $row['roleid'];
                }
                $activities->items[$key]['roles'] = $roles;
            } else {
                $activities->items[$key]['roles'] = [];
            }
        }
        $data['activities'] = $activities->items;

        // Get all the instances
        $instances = $this->data()->getObjectList(['name' => 'workflow_instance_activities']);
        $where = "status = 'running'";
        $instances->getItems(['where' => $where]);
        $data['properties'] = $instances->getProperties();
        $data['items'] = $instances->items;
        return $data;
    }
}
