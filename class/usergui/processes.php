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
use xarSecurity;
use xarUser;
use xarModVars;
use xarTplPager;
use xarServer;
use sys;
use BadParameterException;

sys::import('xaraya.modules.method');

/**
 * workflow user processes function
 * @extends MethodClass<UserGui>
 */
class ProcessesMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * the processes user function
     * @author mikespub
     * @access public
     */
    public function __invoke(array $args = [])
    {
        // Security Check
        if (!xarSecurity::check('ReadWorkflow')) {
            return;
        }

        // Common setup for Galaxia environment
        sys::import('modules.workflow.lib.galaxia.config');
        $data = [];

        // Adapted from tiki-g-user_processes.php
        include_once(GALAXIA_LIBRARY . '/gui.php');

        // Initialize some stuff
        $user = xarUser::getVar('id');
        $maxRecords = xarModVars::get('workflow', 'items_per_page');

        // Filtering data to be received by request and
        // used to build the where part of a query
        // filter_active, filter_valid, find, sort_mode,
        // filter_process
        $where = '';
        $wheres = [];

        /*
        if(isset($_REQUEST['filter_active'])&&$_REQUEST['filter_active']) $wheres[]="isActive='".$_REQUEST['filter_active']."'";
        if(isset($_REQUEST['filter_valid'])&&$_REQUEST['filter_valid']) $wheres[]="isValid='".$_REQUEST['filter_valid']."'";
        if(isset($_REQUEST['filter_process'])&&$_REQUEST['filter_process']) $wheres[]="pId=".$_REQUEST['filter_process']."";
        $where = implode(' and ',$wheres);
        */
        if (!isset($_REQUEST["sort_mode"])) {
            $sort_mode = 'pId_asc';
        } else {
            $sort_mode = $_REQUEST["sort_mode"];
        }

        if (!isset($_REQUEST["offset"])) {
            $offset = 1;
        } else {
            $offset = $_REQUEST["offset"];
        }

        $data['offset'] = &$offset;

        if (isset($_REQUEST["find"])) {
            $find = $_REQUEST["find"];
        } else {
            $find = '';
        }

        $data['find'] =  $find;
        $data['where'] =  $where;
        $data['sort_mode'] = &$sort_mode;

        $items = $GUI->gui_list_user_processes($user, $offset - 1, $maxRecords, $sort_mode, $find, $where);
        $data['cant'] =  $items['cant'];

        $cant_pages = ceil($items["cant"] / $maxRecords);
        $data['cant_pages'] = &$cant_pages;
        $data['actual_page'] =  1 + (($offset - 1) / $maxRecords);

        if ($items["cant"] >= ($offset + $maxRecords)) {
            $data['next_offset'] =  $offset + $maxRecords;
        } else {
            $data['next_offset'] =  -1;
        }

        if ($offset > 1) {
            $data['prev_offset'] =  $offset - $maxRecords;
        } else {
            $data['prev_offset'] =  -1;
        }

        $data['items'] = &$items["data"];

        //$section = 'workflow';
        //include_once ('tiki-section_options.php');

        $sameurl_elements = [
            'offset',
            'sort_mode',
            'where',
            'find',
            'filter_valid',
            'filter_process',
            'filter_active',
            'processId',
        ];

        $data['mid'] =  'tiki-g-user_processes.tpl';


        /*        $data['pager'] = xarTplPager::getPager($data['offset'],
                                                   $items['cant'],
                                                   $url,
                                                   $maxRecords);*/
        $data['url'] = xarServer::getCurrentURL(['offset' => '%%']);
        $data['maxRecords'] = $maxRecords;
        return $data;
    }
}
