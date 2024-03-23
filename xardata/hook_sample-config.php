<?php

// See config/packages/workflow.yaml at https://symfony.com/doc/current/workflow.html
// and corresponding config/workflow.php at https://github.com/zerodahero/laravel-workflow
// See also https://pimcore.com/docs/pimcore/current/Development_Documentation/Workflow_Management/Configuration_Details/index.html

// return configuration of the workflow
return [
    'name' => 'hook_sample',
    'label' => 'Hook Sample',
    'description' => "Experiment with hook events triggering workflow transitions",
    'type' => 'state_machine',
    'supports' => ['sample'],  // DynamicData Object this workflow should apply to
    'create_object' => false,  // create the DynamicData Object if it doesn't exist
    'initial_marking' => ['waiting'],
    'places' => [
        'waiting',
        //'hooked',
        'created',
        'updated',
        'deleted',
        //'displayed',
        'closed',
    ],
    'transitions' => [
        //'hook event' => [
        //    'from' => ['waiting'],
        //    'to' => ['hooked'],
        //],
        'create_event' => [
            'from' => ['waiting'],
            'to' => ['created'],
        ],
        'update_event' => [
            'from' => ['created', 'updated'],
            'to' => ['updated'],
        ],
        'delete_event' => [
            'from' => ['created', 'updated'],
            'to' => ['deleted'],
        ],
        'acknowledge' => [
            //'from' => ['hooked'],
            'from' => ['created', 'updated', 'deleted'],
            'to' => ['closed'],
            'admin' => true,
            'delete' => true,
        ],
    ],
];
