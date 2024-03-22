<?php

// See config/packages/workflow.yaml at https://symfony.com/doc/current/workflow.html
// and corresponding config/workflow.php at https://github.com/zerodahero/laravel-workflow
// See also https://pimcore.com/docs/pimcore/current/Development_Documentation/Workflow_Management/Configuration_Details/index.html

// See https://symfony.com/doc/current/workflow/workflow-and-state-machine.html
// return configuration of the workflow
return [
    'name' => 'pull_request',
    'label' => 'Pull Request',
    'description' => "Pull Request",
    'type' => 'state_machine',
    'supports' => ['pull_requests'],  // DynamicData Object this workflow should apply to
    'create_object' => false,  // create the DynamicData Object if it doesn't exist
    'initial_marking' => ['start'],
    'places' => [
        'start',
        'coding',
        'test',
        'review',
        'merged',
        'closed',
    ],
    'transitions' => [
        'submit' => [
            'from' => ['start'],
            'to' => ['test'],
        ],
        'update' => [
            'from' => ['coding', 'test', 'review'],
            'to' => ['test'],
        ],
        'wait_for_review' => [
            'from' => ['test'],
            'to' => ['review'],
        ],
        'request_change' => [
            'from' => ['review'],
            'to' => ['coding'],
        ],
        'accept' => [
            'from' => ['review'],
            'to' => ['merged'],
        ],
        'reject' => [
            'from' => ['review'],
            'to' => ['closed'],
        ],
        'reopen' => [
            'from' => ['closed'],
            'to' => ['review'],
        ],
    ],
];
