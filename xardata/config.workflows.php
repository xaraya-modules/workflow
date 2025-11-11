<?php

// See config/packages/workflow.yaml at https://symfony.com/doc/current/workflow.html
// and corresponding config/workflow.php at https://github.com/zerodahero/laravel-workflow
// See also https://pimcore.com/docs/pimcore/current/Development_Documentation/Workflow_Management/Configuration_Details/index.html

use Xaraya\Modules\Workflow\WorkflowConfig;

// Load workflow configuration(s) from *-config.php file(s) in this directory
// return configuration of the workflow(s)
return WorkflowConfig::loadDir(__DIR__, '-config.php');
