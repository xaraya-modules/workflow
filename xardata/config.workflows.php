<?php

// See config/packages/workflow.yaml at https://symfony.com/doc/current/workflow.html
// and corresponding config/workflow.php at https://github.com/zerodahero/laravel-workflow
// See also https://pimcore.com/docs/pimcore/current/Development_Documentation/Workflow_Management/Configuration_Details/index.html

/**
 * Load workflow configuration(s) from *-config.php file(s) in this directory
 *
 * @param string $dirPath this directory
 * @param string $suffix (default)
 * @return array<string, mixed>
 */
function workflow_config_loader($dirPath, $suffix = '-config.php')
{
    $config = [];
    $fileList = scandir($dirPath);
    foreach ($fileList as $fileName) {
        if (!str_ends_with($fileName, $suffix)) {
            continue;
        }
        $filePath = $dirPath . '/' . $fileName;
        $info = workflow_config_loadfile($filePath);
        if (empty($info)) {
            continue;
        }
        $info['name'] ??= str_replace($suffix, '', $fileName);
        $config[$info['name']] = $info;
    }
    return $config;
}

/**
 * Load workflow configuration file
 *
 * @param string $filePath
 * @return array<string, mixed>
 */
function workflow_config_loadfile($filePath)
{
    $info = require $filePath;
    return $info;
}

// return configuration of the workflow(s)
return workflow_config_loader(__DIR__);
