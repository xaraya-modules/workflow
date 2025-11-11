<?php

/**
 * This workflow show-cases several special features:
 *
 * 1. it uses a multiState workflow instead of a singleState state machine
 * 2. its configuration is loaded from a json file directly converted from a symfony workflow.yaml file
 * 3. it has a callback function to automatically run a (dummy) spell checker once request_review is completed
 */

use Xaraya\Modules\Workflow\WorkflowUtils;
use Xaraya\Modules\Publications\SpellCheckerDummy;

/**
 * See https://github.com/lyrixx/SFLive-Paris2016-Workflow/blob/master/config/packages/workflow.yaml
 *
 * Note: convert workflow.yaml to json online first to avoid needing yaml parser
 */
$jsonText = '
{
    "article": {
        "type": "workflow",
        "marking_store": {
            "type": "method"
        },
        "supports": [
            "App\\\\Entity\\\\Article"
        ],
        "places": {
            "draft": {
                "metadata": {
                    "title": "Draft"
                }
            },
            "waiting for journalist": {
                "metadata": {
                    "title": "Waiting for Journalist review"
                }
            },
            "approved by journalist": {
                "metadata": {
                    "title": "Approved By Journalist"
                }
            },
            "wait for spellchecker": {
                "metadata": {
                    "title": "Waiting for Spellchecker review"
                }
            },
            "approved by spellchecker": {
                "metadata": {
                    "title": "Approved By Spellchecker"
                }
            },
            "published": null
        },
        "transitions": {
            "request review": {
                "guard": "is_fully_authenticated()",
                "from": "draft",
                "to": [
                    "waiting for journalist",
                    "wait for spellchecker"
                ],
                "metadata": {
                    "title": "Do you want a Review?"
                }
            },
            "journalist approval": {
                "guard": "is_granted(\'ROLE_JOURNALIST\')",
                "from": "waiting for journalist",
                "to": "approved by journalist",
                "metadata": {
                    "title": "Do you valid the article?"
                }
            },
            "spellchecker approval": {
                "guard": "is_fully_authenticated() and is_granted(\'ROLE_SPELLCHECKER\')",
                "from": "wait for spellchecker",
                "to": "approved by spellchecker",
                "metadata": {
                    "title": "Do you valid the spell check?"
                }
            },
            "publish": {
                "guard": "is_fully_authenticated()",
                "from": [
                    "approved by journalist",
                    "approved by spellchecker"
                ],
                "to": "published",
                "metadata": {
                    "title": "Do you want to publish?"
                }
            }
        },
        "metadata": {
            "title": "Manage article"
        },
        "audit_trail": true
    }
}
';

// list of callback functions per workflow, transition & event type
$callbackFuncs = [
    // here you can specify callback functions to run the spell checker once the transition is completed
    'article.completed.request_review' => SpellCheckerDummy::startSpellCheckerHandler(
        ['title'],                // fields to spell check for the article dataobject
        'spellchecker_approval',  // transition in case of success
        '',                       // transition in case of failure
        true                      // queue this event for later processing or not
    ),
];

$workflowName = 'article';
$objectName = 'wf_' . $workflowName;

// load configuration from json text
$config = WorkflowUtils::convertJsonToConfig($jsonText, $workflowName, $objectName);
// add callback function on completed event to start spell checker
$config['transitions']['request_review']['completed'] = $callbackFuncs['article.completed.request_review'];
// or queue the event for later processing here - @todo how to specify event handler then?
//$config['transitions']['request_review']['queue'] = true;

// return configuration of the workflow
return $config;
