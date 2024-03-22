<?php
/**
 * See https://github.com/lyrixx/SFLive-Paris2016-Workflow/blob/master/config/packages/workflow.yaml
 *
 * Note: convert yaml to json online first to avoid needing yaml parser
 */

$json = '
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

$data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);

$workflow = 'article';

$config = [];
$config['name'] = $workflow;
$config['type'] = 'workflow';
$config['metadata'] = $data[$workflow]['metadata'] ?? [];
$config['label'] = $config['metadata']['title'] ?? ucwords($workflow);
$config['description'] = $config['metadata']['description'] ?? $config['label'];
$config['supports'] = ['wf_' . $workflow];
$config['create_object'] = true;
$config['places'] = [];
foreach (array_keys($data[$workflow]['places']) as $place) {
    $config['places'][] = str_replace(' ', '_', $place);
}
$config['initial_marking'] = [$config['places'][0]];
$config['transitions'] = [];
foreach (array_keys($data[$workflow]['transitions']) as $transition) {
    $name = str_replace(' ', '_', $transition);
    $config['transitions'][$name] = [];
    $from = $data[$workflow]['transitions'][$transition]['from'];
    if (is_array($from)) {
        $config['transitions'][$name]['from'] = [];
        //$fromplaces = [];
        foreach ($from as $place) {
            // for workflow this means AND-ing!?
            //$fromplaces[] = str_replace(' ', '_', $place);
            // for workflow this means OR-ing!?
            $config['transitions'][$name]['from'][] = str_replace(' ', '_', $place);
        }
        //$config['transitions'][$name]['from'][] = $fromplaces;
    } else {
        $config['transitions'][$name]['from'] = [str_replace(' ', '_', $from)];
    }
    $to = $data[$workflow]['transitions'][$transition]['to'];
    if (is_array($to)) {
        $config['transitions'][$name]['to'] = [];
        foreach ($to as $place) {
            $config['transitions'][$name]['to'][] = str_replace(' ', '_', $place);
        }
    } else {
        $config['transitions'][$name]['to'] = [str_replace(' ', '_', $to)];
    }
    $metadata = $data[$workflow]['transitions'][$transition]['metadata'] ?? null;
    if (!empty($metadata)) {
        $config['transitions'][$name]['metadata'] = $metadata;
    }
}

// return configuration of the workflow
return $config;
