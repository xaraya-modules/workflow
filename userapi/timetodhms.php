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
 * workflow userapi timetodhms function
 * @extends MethodClass<UserApi>
 */
class TimetodhmsMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup * @see UserApi::timetodhms()
     */

    public function __invoke(array $args = [])
    {
        extract($args);
        if (!isset($format)) {
            $format = '';
        }

        if ($time > 24 * 60 * 60) {
            $days = intval($time / (24 * 60 * 60));
            $time = $time % (24 * 60 * 60);
        } else {
            $days = 0;
        }
        if ($time > 60 * 60) {
            $hours = intval($time / (60 * 60));
            $time = $time % (60 * 60);
        } else {
            $hours = 0;
        }
        if ($time > 60) {
            $minutes = intval($time / 60);
            $time = $time % 60;
        } else {
            $minutes = 0;
        }
        $seconds = intval($time);
        if (!empty($format)) {
            // decide on some format :-)
        } elseif (!empty($days)) {
            $out = $this->ml('#(1)d #(2)h #(3)m #(4)s', $days, $hours, $minutes, $seconds);
        } elseif (!empty($hours)) {
            $out = $this->ml('#(1)h #(2)m #(3)s', $hours, $minutes, $seconds);
        } elseif (!empty($minutes)) {
            $out = $this->ml('#(1)m #(2)s', $minutes, $seconds);
        } elseif (!empty($seconds)) {
            $out = $this->ml('#(1)s', $seconds);
        } else {
            $out = '';
        }
        return $out;
    }
}
