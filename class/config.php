<?php
/**
 * Workflow Module Configuration for Symfony Workflow events
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

namespace Xaraya\Modules\Workflow;

use xarObject;
use xarServer;
use sys;
use Exception;

class WorkflowConfig extends xarObject
{
    public static $config = [];

    public static function init(array $args = [])
    {
        static::loadConfig();
        //static::setAutoload();
    }

    public static function loadConfig()
    {
        if (!empty(static::$config)) {
            return static::$config;
        }
        static::$config = [];
        //$configFile = sys::varpath() . '/cache/processes/config.json';
        $configFile = dirname(__DIR__) . '/xardata/config.workflows.php';
        if (file_exists($configFile)) {
            //$contents = file_get_contents($configFile);
            //static::$config = json_decode($contents, true);
            static::$config = include($configFile);
        }
        return static::$config;
    }

    public static function formatName(string $name)
    {
        return ucwords(str_replace('_', ' ', $name));
    }

    public static function checkAutoload()
    {
        // Adapted from sys::autoload()
        if (empty(sys::$root)) {
            // go back up to directory above bootstrap.php (for composer install with symlinks)
            $vendor = dirname(__DIR__, 4) . '/vendor';
        } elseif (sys::$root == sys::$web && is_dir(sys::$root . '../vendor')) {
            $vendor = sys::$root . '../vendor';
        } else {
            $vendor = sys::$root . 'vendor';
        }

        if (!file_exists($vendor . '/autoload.php')) {
            $message = <<<EOT
                This test needs composer autoload to run the workflows
                $ composer require --dev symfony/workflow
                ...
                $ head html/code/modules/workflow/xaruser/test_run.php
                &lt;?php
                sys::autoload();
                ...
                EOT;
            throw new Exception($message . "\nVendor: $vendor\n");
        }
        return $vendor . '/autoload.php';
    }

    public static function setAutoload()
    {
        static::checkAutoload();
        sys::autoload();
    }

    public static function hasWorkflowConfig(string $workflowName)
    {
        static::loadConfig();
        if (!empty(static::$config) && !empty(static::$config[$workflowName])) {
            return true;
        }
        return false;
    }

    public static function getWorkflowConfig(string $workflowName)
    {
        if (!static::hasWorkflowConfig($workflowName)) {
            throw new Exception('Unknown workflow ' . $workflowName);
        }
        return static::$config[$workflowName];
    }

    public static function getInitialMarking(string $workflowName)
    {
        $config = static::getWorkflowConfig($workflowName);
        return is_array($config['initial_marking']) ? $config['initial_marking'][0] : $config['initial_marking'];
    }
}
