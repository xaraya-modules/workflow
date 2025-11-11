<?php

// See config/packages/workflow.yaml at https://symfony.com/doc/current/workflow.html
// and corresponding config/workflow.php at https://github.com/zerodahero/laravel-workflow
// See also https://pimcore.com/docs/pimcore/current/Development_Documentation/Workflow_Management/Configuration_Details/index.html

use Xaraya\Modules\Workflow\WorkflowHandlers;

// list of callback functions per workflow, transition & event type
$callbackFuncs = [
    // here you can specify callback functions as transition blockers - expression language is not supported
    'cd_loans.guard.request' => WorkflowHandlers::guardCheckProperty([
        'cdcollection' => ['status' => 'available'],
    ]),
    // here you can specify callback functions to update the actual objects once the transition is completed
    'cd_loans.completed.retrieve' => WorkflowHandlers::updateProperty([
        'cdcollection' => ['status' => 'not available'],
    ]),
    'cd_loans.completed.return' => WorkflowHandlers::updateProperty([
        'cdcollection' => ['status' => 'available'],
    ]),
];

// return configuration of the workflow
return [
    'name' => 'cd_loans',
    'label' => 'Music CD Loans',
    'description' => "Borrow CD's, browse CDs, etc...",
    //'class' => null,  // something other than Workflow or StateMachine - not supported here
    //'type' => 'workflow',
    'type' => 'state_machine',
    //'marking_store' => [
    //    'type' => 'method',
    //    'property' => 'marking'  // this assumes the subject has methods getMarking() and setMarking()
    //],
    //'metadata' => [],
    'supports' => ['cdcollection'],  // DynamicData Object this workflow should apply to
    'create_object' => false,  // create the DynamicData Object if it doesn't exist
    // you can pass one or more event names, or pass an empty array to not dispatch any event
    //'events_to_dispatch' => [],
    'initial_marking' => ['available'],
    'places' => [
        'available',
        'requested',
        'approved',
        'rejected',
        'escalated',
        'acknowledged',
        'retrieved',
        'returned',
        'deleted',
        'not_available',
    ],
    // @todo custom templates to use for particular places?
    'templates' => [
    ],
    'transitions' => [
        'request' => [
            // See https://github.com/symfony/symfony/blob/6.3/src/Symfony/Bundle/FrameworkBundle/DependencyInjection/FrameworkExtension.php#L917
            // we mean from ANY here, not from ALL like default for workflow
            //'from' => ['available', 'requested', 'rejected', 'escalated', 'acknowledged', 'returned'],
            'from' => ['available'],
            'to' => ['requested'],
            // here you can specify callback functions as transition blockers - expression language is not supported
            //'guard' => $callbackFuncs['cd_loans.guard.request'],
            // or you can check permission to run the transition in predefined ways
            'property' => ['cdcollection' => ['status' => 'available']],  // check if this property of the object has that value
            //'admin' => false,                               // true or false userId belongs to admin role groups
            //'roles' => ['administrators', 'sitemanagers'],  // parent role groups the userId can belong to
            'access' => 'update',                             // $dataobject->checkAccess()
            //'security' => 'SubmitWorkflow',                 // xarSecurity::check()
        ],
        'approve' => [
            'from' => 'requested',
            'to' => 'approved',
            'admin' => true,
            //'roles' => ['administrators', 'sitemanagers'],
        ],
        'retrieve' => [
            'from' => 'approved',
            'to' => ['retrieved', 'not_available'],  // two places not supported for state_machine, pick the first
            // here you can specify callback functions to update the actual objects once the transition is completed
            //'completed' => $callbackFuncs['cd_loans.completed.retrieve'],
            // or you can update the actual objects in predefined ways
            'update' => ['cdcollection' => ['status' => 'not available']],
        ],
        'reject' => [
            'from' => 'requested',
            'to' => 'rejected',
            //'admin' => true,
            'roles' => ['administrators', 'sitemanagers'],
        ],
        'acknowledge' => [
            'from' => 'rejected',
            'to' => 'acknowledged',
        ],
        'escalate' => [
            'from' => 'requested',
            'to' => 'escalated',
            'roles' => ['scheduler'],
        ],
        'timeout' => [
            'from' => 'escalated',
            'to' => 'rejected',
        ],
        'return' => [
            'from' => 'retrieved',
            'to' => ['returned', 'available'],  // two places not supported for state_machine, pick the first
            // here you can specify callback functions to update the actual objects once the transition is completed
            'completed' => $callbackFuncs['cd_loans.completed.return'],
            // or you can update the actual objects in predefined ways
            //'update' => ['cdcollection' => ['status' => 'available']],
        ],
        'close' => [
            'from' => ['returned', 'acknowledged'],
            'to' => 'deleted',
            // here you can specify that this workflow is finished and we can delete the tracker
            'delete' => true,
        ],
    ],
];
