<?php

/**
 * the workitem administration function
 * 
 * @author mikespub
 * @access public 
 */
function workflow_admin_workitem()
{
    // Security Check
    if (!xarSecurityCheck('AdminWorkflow')) return;

// Common setup for Galaxia environment
    include_once('modules/workflow/tiki-setup.php');
    $tplData = array();

// Adapted from tiki-g-view_workitem.php

include_once (GALAXIA_DIR.'/ProcessMonitor.php');

if ($feature_workflow != 'y') {
	$tplData['msg'] =  xarML("This feature is disabled");

	return xarTplModule('workflow', 'admin', 'error', $tplData);
	die;
}

if ($tiki_p_admin_workflow != 'y') {
	$tplData['msg'] =  xarML("Permission denied");

	return xarTplModule('workflow', 'admin', 'error', $tplData);
	die;
}

if (!isset($_REQUEST['itemId'])) {
	$tplData['msg'] =  xarML("No item indicated");

	return xarTplModule('workflow', 'admin', 'error', $tplData);
	die;
}

$wi = $processMonitor->monitor_get_workitem($_REQUEST['itemId']);
if (is_numeric($wi['user'])) {
    $wi['user'] = xarUserGetVar('name',$wi['user']);
}
$tplData['wi'] =&  $wi;

$tplData['stats'] =  $processMonitor->monitor_stats();

$sameurl_elements = array(
	'offset',
	'sort_mode',
	'where',
	'find',
	'itemId'
);

$tplData['mid'] =  'tiki-g-view_workitem.tpl';

    if (count($smarty->tplData) > 0) {
       foreach (array_keys($smarty->tplData) as $key) {
           $tplData[$key] = $smarty->tplData[$key];
       }
    }
    $tplData['feature_help'] = $feature_help;
    $tplData['direct_pagination'] = $direct_pagination;
    return $tplData;
}

?>