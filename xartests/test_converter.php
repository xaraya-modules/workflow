<?php
/**
 * Workflow Module Test Script for Blocklayout Converter tests
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
use Xaraya\Bridge\TemplateEngine\BlocklayoutToTwigConverter;

if (php_sapi_name() !== 'cli') {
    echo 'Workflow Module Test Script for Blocklayout Converter tests';
    return;
}

$baseDir = dirname(__DIR__);
$baseDir = '/home/mikespub/xaraya-core';
require_once $baseDir . '/vendor/autoload.php';

// convert all test_*.xt templates from includes directory
$options = [
    'namespace' => 'workflow/includes',
];
$converter = new BlocklayoutToTwigConverter($options);
$sourcePath = dirname(__DIR__) . '/xartemplates/includes';
$targetPath = dirname(__DIR__) . '/templates/includes2';
$converter->convertDir($sourcePath, $targetPath, '.xt', 'test_');
