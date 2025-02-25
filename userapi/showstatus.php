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
use Xaraya\Modules\MethodClass;
use xarSecurity;
use xarUser;
use xarModUserVars;
use xarSession;
use xarServer;
use xarTpl;
use sys;
use BadParameterException;

sys::import('xaraya.modules.method');

/**
 * workflow userapi showstatus function
 * @extends MethodClass<UserApi>
 */
class ShowstatusMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * show the current status of "your" instances, i.e. those that you started and
     * are the owner of (called via <xar:workflow-status tag)
     * @author mikespub
     * @access public
     * @see UserApi::showstatus()
     */
    public function __invoke(array $args = [])
    {
        // Security Check
        if (!$this->sec()->checkAccess('ReadWorkflow', 0)) {
            return '';
        }

        // Common setup for Galaxia environment
        sys::import('modules.workflow.lib.galaxia.config');
        $tplData = [];

        include(GALAXIA_LIBRARY . '/processmonitor.php');

        if (empty($user)) {
            $user = $this->user()->getId();
        }

        // TODO: keep track of instances from anonymous visitors via session ?

        // retrieve the instances for which you're the owner
        $where = "owner=$user";
        if (!empty($args['status'])) {
            if (strpos($args['status'], ',')) {
                $statuslist = explode(',', $args['status']);
                $where .= " and gi.status IN ('" . implode("','", $statuslist) . "')";
            } else {
                $where .= " and gi.status='" . $args['status'] . "'";
            }
        }
        if (!empty($args['notstatus'])) {
            if (strpos($args['notstatus'], ',')) {
                $statuslist = explode(',', $args['notstatus']);
                $where .= " and gi.status NOT IN ('" . implode("','", $statuslist) . "')";
            } else {
                $where .= " and gi.status!='" . $args['notstatus'] . "'";
            }
        }
        if (!empty($args['actstatus'])) {
            $where .= " and gia.status='" . $args['actstatus'] . "'";
        }
        if (!empty($args['pId'])) {
            $where .= " and gp.pId='" . $args['pId'] . "'";
        }
        if (!empty($args['owner'])) {
            $where .= " and gi.owner='" . $args['owner'] . "'";
        }
        if (!empty($args['user'])) {
            $where .= " and gia.user='" . $args['user'] . "'";
        }
        if (!empty($args['numitems'])) {
            $numitems = $args['numitems'];
        } else {
            $numitems = -1;
        }
        if (!empty($args['startnum'])) {
            $startnum = $args['startnum'];
        } else {
            $startnum = 1;
        }
        if (!empty($args['sort_mode'])) {
            $sort_mode = $args['sort_mode'];
        } else {
            $sort_mode = 'started_asc';
        }
        $items = $processMonitor->monitor_list_instances($startnum - 1, $numitems, $sort_mode, '', $where, []);

        // filter out instances the user doesn't want to see
        if ($this->user()->isLoggedIn()) {
            $seenlist = $this->mod()->getUserVar('seenlist');
        } else {
            $seenlist = $this->session()->getVar('workflow.seenlist');
        }
        if (!empty($seenlist)) {
            $seen = explode(';', $seenlist);
        } else {
            $seen = [];
        }
        $tplData['items'] = [];
        foreach ($items['data'] as $index => $info) {
            if (in_array($info['instanceId'], $seen)) {
                continue;
            }
            $tplData['items'][] = $items['data'][$index];
        }
        if (count($tplData['items']) < 1) {
            return '';
        }

        $tplData['userId'] = $user;

        if (!empty($args['title'])) {
            $tplData['title'] = $args['title'];
        }

        if (!empty($args['layout'])) {
            $tplData['layout'] = $args['layout'];
        }

        // URL to return to if some action is taken
        if (!empty($args['return_url'])) {
            $tplData['return_url'] = $args['return_url'];
        } else {
            $tplData['return_url'] = $this->ctl()->getCurrentURL();
        }

        // field list to show
        if (!empty($args['fieldlist'])) {
            $tplData['fieldlist'] = explode(',', $args['fieldlist']);
        }

        // action list to show (could be empty here !)
        if (isset($args['actionlist'])) {
            if (!empty($args['actionlist'])) {
                $tplData['actionlist'] = explode(',', $args['actionlist']);
            } else {
                $tplData['actionlist'] = [];
            }
        }
        $tplData['context'] ??= $this->getContext();

        if (!empty($args['template'])) {
            return $this->mod()->template('showstatus', $tplData, $args['template']);
        } else {
            return $this->mod()->template('showstatus', $tplData);
        }
    }
}
