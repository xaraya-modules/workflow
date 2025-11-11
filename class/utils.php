<?php

/**
 * Workflow Module Utilities for Symfony Workflow events
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

use JsonException;

class WorkflowUtils extends WorkflowBase
{
    /**
     * See https://github.com/lyrixx/SFLive-Paris2016-Workflow/blob/master/config/packages/workflow.yaml
     *
     * Note: convert workflow.yaml to json online first to avoid needing yaml parser
     *
     * @throws JsonException
     * @return array<string, mixed>
     */
    public static function convertJsonToConfig(string $jsonText, string $workflowName, ?string $objectName = null)
    {
        /** @var array<string, array<string, mixed>> $data */
        $data = json_decode($jsonText, true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($data) || empty($data[$workflowName])) {
            throw new JsonException("JSON text does not describe workflow '{$workflowName}'");
        }
        $objectName ??= 'wf_' . $workflowName;
        $workflow = $data[$workflowName];

        $config = [];
        $config['name'] = $workflow['name'] ?? $workflowName;
        $config['type'] = $workflow['type'] ?? 'workflow';
        $config['metadata'] = $workflow['metadata'] ?? [];
        $config['label'] = $config['metadata']['title'] ?? WorkflowConfig::formatName($workflowName);
        $config['description'] = $config['metadata']['description'] ?? $config['label'];
        $config['supports'] = [$objectName];
        $config['create_object'] = true;
        $config['places'] = static::convertPlaces($workflow['places']);
        $config['initial_marking'] = [$config['places'][0]];
        $config['transitions'] = static::convertTransitions($workflow['transitions']);

        return $config;
    }

    /**
     * @param array<string, mixed> $places
     * @return list<string>
     */
    public static function convertPlaces(array $places)
    {
        $info = [];
        foreach (array_keys($places) as $place) {
            $info[] = str_replace(' ', '_', $place);
        }
        return $info;
    }

    /**
     * @param array<string, array<string, mixed>> $transitions
     * @return array<string, array<string, mixed>>
     */
    public static function convertTransitions(array $transitions)
    {
        $info = [];
        foreach ($transitions as $name => $transition) {
            $name = str_replace(' ', '_', $name);
            $info[$name] = [];
            $from = $transition['from'];
            if (is_array($from)) {
                $info[$name]['from'] = [];
                //$fromplaces = [];
                foreach ($from as $place) {
                    // for workflow this means OR-ing!?
                    //$fromplaces[] = str_replace(' ', '_', $place);
                    // for workflow this means AND-ing!?
                    $info[$name]['from'][] = str_replace(' ', '_', $place);
                }
                //$info[$name]['from'][] = $fromplaces;
            } else {
                $info[$name]['from'] = [str_replace(' ', '_', (string) $from)];
            }
            $to = $transition['to'];
            if (is_array($to)) {
                $info[$name]['to'] = [];
                foreach ($to as $place) {
                    $info[$name]['to'][] = str_replace(' ', '_', $place);
                }
            } else {
                $info[$name]['to'] = [str_replace(' ', '_', (string) $to)];
            }
            $metadata = $transition['metadata'] ?? null;
            if (!empty($metadata)) {
                $info[$name]['metadata'] = $metadata;
            }
        }
        return $info;
    }
}
