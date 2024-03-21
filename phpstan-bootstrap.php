<?php

//require_once __DIR__ . '/vendor/autoload.php';
//sys::init();
define('GALAXIA_LIBRARY', './lib/galaxia');
define('GALAXIA_PROCESSES', 'var/processes');
if (!function_exists('xarML')) {
    /**
     * Summary of xarML
     * @param mixed $rawstring
     * @param array<mixed> $args
     * @return mixed
     */
    function xarML($rawstring, ...$args)
    {
        return call_user_func_array(array('xarMLS', 'translate'), func_get_args());
    }
}
