<?php

/**
 * the monitor workitems administration function
 * 
 * @author mikespub
 * @access public 
 */
function workflow_admin_monitor_workitems()
{
    // Security Check
    if (!xarSecurityCheck('AdminWorkflow')) return;

// Common setup for Galaxia environment
    include_once('modules/workflow/tiki-setup.php');
    $tplData = array();

// Adapted from tiki-g-monitor_workitems.php

include_once (GALAXIA_DIR.'/ProcessMonitor.php');

if ($feature_workflow != 'y') {
	$tplData['msg'] =  xarML("This feature is disabled");

	return xarTplModule('workflow', 'monitor', 'error', $tplData);
	die;
}

if ($tiki_p_admin_workflow != 'y') {
	$tplData['msg'] =  xarML("Permission denied");

	return xarTplModule('workflow', 'monitor', 'error', $tplData);
	die;
}

// Filtering data to be received by request and
// used to build the where part of a query
// filter_active, filter_valid, find, sort_mode,
// filter_process
$where = '';
$wheres = array();

if (isset($_REQUEST['filter_instance']) && $_REQUEST['filter_instance'])
	$wheres[] = "instanceId=" . $_REQUEST['filter_instance'] . "";

if (isset($_REQUEST['filter_process']) && $_REQUEST['filter_process'])
	$wheres[] = "gp.pId=" . $_REQUEST['filter_process'] . "";

if (isset($_REQUEST['filter_activity']) && $_REQUEST['filter_activity'])
	$wheres[] = "ga.activityId=" . $_REQUEST['filter_activity'] . "";

if (isset($_REQUEST['filter_user']) && $_REQUEST['filter_user'])
	$wheres[] = "user='" . $_REQUEST['filter_user'] . "'";

$where = implode(' and ', $wheres);

if (!isset($_REQUEST["sort_mode"])) {
	$sort_mode = 'orderId_asc';
} else {
	$sort_mode = $_REQUEST["sort_mode"];
}

if (!isset($_REQUEST["offset"])) {
	$offset = 0;
} else {
	$offset = $_REQUEST["offset"];
}

$tplData['offset'] =&  $offset;

if (isset($_REQUEST["find"])) {
	$find = $_REQUEST["find"];
} else {
	$find = '';
}

$tplData['find'] =  $find;
$tplData['where'] =  $where;
$tplData['sort_mode'] =&  $sort_mode;

$items = $processMonitor->monitor_list_workitems($offset, $maxRecords, $sort_mode, $find, $where);
$tplData['cant'] =  $items['cant'];

$cant_pages = ceil($items["cant"] / $maxRecords);
$tplData['cant_pages'] =&  $cant_pages;
$tplData['actual_page'] =  1 + ($offset / $maxRecords);

if ($items["cant"] > ($offset + $maxRecords)) {
	$tplData['next_offset'] =  $offset + $maxRecords;
} else {
	$tplData['next_offset'] =  -1;
}

if ($offset > 0) {
	$tplData['prev_offset'] =  $offset - $maxRecords;
} else {
	$tplData['prev_offset'] =  -1;
}

foreach (array_keys($items['data']) as $index) {
    if (!is_numeric($items['data'][$index]['user'])) continue;
    $items['data'][$index]['user'] = xarUserGetVar('name',$items['data'][$index]['user']);
}
$tplData['items'] =&  $items["data"];

$all_procs = $items = $processMonitor->monitor_list_processes(0, -1, 'name_desc', '', '');
$tplData['all_procs'] =&  $all_procs["data"];

if (isset($_REQUEST['filter_process']) && $_REQUEST['filter_process']) {
	$where = ' pId=' . $_REQUEST['filter_process'];
} else {
	$where = '';
}

$all_acts = $processMonitor->monitor_list_activities(0, -1, 'name_desc', '', $where);
$tplData['all_acts'] =&  $all_acts["data"];

$sameurl_elements = array(
	'offset',
	'sort_mode',
	'where',
	'find',
	'filter_user',
	'filter_activity',
	'filter_process',
	'filter_instance',
	'processId',
	'filter_process'
);

$types = $processMonitor->monitor_list_activity_types();
$tplData['types'] =&  $types;

$tplData['stats'] =  $processMonitor->monitor_stats();

$users = $processMonitor->monitor_list_wi_users();
$tplData['users'] = array();
foreach (array_keys($users) as $index) {
    if (!is_numeric($users[$index])) {
        $tplData['users'][$index]['user'] = $users[$index];
        $tplData['users'][$index]['userId'] = $users[$index];
    } else {
        $tplData['users'][$index]['user'] = xarUserGetVar('name',$users[$index]);
        $tplData['users'][$index]['userId'] = $users[$index];
    }
}

$tplData['mid'] =  'tiki-g-monitor_workitems.tpl';

// Missing variables
$tplData['filter_process'] = isset($_REQUEST['filter_process']) ? $_REQUEST['filter_process'] : '';
$tplData['filter_activity'] = isset($_REQUEST['filter_activity']) ? $_REQUEST['filter_activity'] : '';
$tplData['filter_user'] = isset($_REQUEST['filter_user']) ? $_REQUEST['filter_user'] : '';
$tplData['filter_instance'] = isset($_REQUEST['filter_instance']) ? $_REQUEST['filter_instance'] : '';

    if (count($smarty->tplData) > 0) {
       foreach (array_keys($smarty->tplData) as $key) {
           $tplData[$key] = $smarty->tplData[$key];
       }
    }
    $tplData['feature_help'] = $feature_help;
    $tplData['direct_pagination'] = $direct_pagination;
    $url = xarServerGetCurrentURL(array('offset' => '%%'));
    $tplData['pager'] = xarTplGetPager($tplData['offset'],
                                       $url,
                                       $items['cant'],
                                       $maxRecords);
    return $tplData;
}

?>