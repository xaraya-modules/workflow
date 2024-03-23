<?php
/**
 * See https://github.com/lyrixx/SFLive-Paris2016-Workflow/blob/master/config/packages/workflow.yaml
 *
 * Note: convert yaml to json online first to avoid needing yaml parser
 */
namespace Xaraya\Modules\Publications;

use Xaraya\Modules\Workflow\WorkflowUtils;
use sys;
sys::import('modules.workflow.class.utils');

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

$workflowName = 'article';
$objectName = 'wf_' . $workflowName;

$config = WorkflowUtils::convertJsonToConfig($jsonText, $workflowName, $objectName);

// return configuration of the workflow
return $config;
