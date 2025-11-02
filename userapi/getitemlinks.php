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
use sys;

sys::import('xaraya.modules.method');

/**
 * workflow userapi getitemlinks function
 * @extends MethodClass<UserApi>
 */
class GetitemlinksMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * utility function to pass individual item links to whoever
     * @param array<mixed> $args
     * @var mixed $itemtype item type (optional)
     * @var mixed $itemids array of item ids to get
     * @return array containing the itemlink(s) for the item(s).
     * @see UserApi::getitemlinks()
     */
    public function __invoke(array $args = [])
    {
        extract($args);

        $itemlinks = [];
        if (empty($itemtype)) {
            return $itemlinks;
        }

        // Common setup for Galaxia environment
        require_once dirname(__DIR__) . '/lib/galaxia/config.php';
        include(\GALAXIA_LIBRARY . '/gui.php');

        if (empty($user)) {
            $user = $this->user()->getId() ?? 0;
        }

        // get the instances this user has access to
        $sort = 'pId_asc, instanceId_asc';
        $find = '';
        $where = "gi.pId=$itemtype";
        $items = $GUI->gui_list_user_instances($user, 0, -1, $sort, $find, $where);

        // TODO: add the instances you're the owner of (if relevant)

        if (empty($items['data']) || !is_array($items['data']) || count($items['data']) == 0) {
            return $itemlinks;
        }

        $itemid2key = [];
        foreach ($items['data'] as $key => $item) {
            $itemid2key[$item['instanceId']] = $key;
        }
        // if we didn't have a list of itemids, return all the items we found
        if (empty($itemids)) {
            $itemids = array_keys($itemid2key);
        }
        foreach ($itemids as $itemid) {
            if (!isset($itemid2key[$itemid])) {
                continue;
            }
            $item = $items['data'][$itemid2key[$itemid]];
            $itemlinks[$itemid] = ['url'   => $this->mod()->getURL(
                'user',
                'instances',
                ['filter_process' => $itemtype]
            ),
                'title' => $this->ml('Display Instance'),
                'label' => $this->prep()->text($item['procname'] . ' ' . $item['version'] . ' # ' . $item['instanceId']), ];
        }
        return $itemlinks;
    }
}
