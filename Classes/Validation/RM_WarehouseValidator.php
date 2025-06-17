<?php

namespace Validation;

class RM_WarehouseValidator
{
    public static function validateStageProcessing(array $data): array
    {
        $errors = [];

        if (empty($data['id']) || empty($data['material_no']) || empty($data['component_name'])) {
            $errors[] = "Missing required fields: id, material_no, or component_name.";
        }

        return $errors;
    }
    public static function flattenStages(array $stageGroup): array
    {
        $flattened = [];

        foreach ($stageGroup as $section => $stages) {
            // If the section contains a 'stages' subkey (nested structure)
            if (is_array($stages) && isset($stages['stages']) && is_array($stages['stages'])) {
                foreach ($stages['stages'] as $stageName => $value) {
                    $flattened[] = [
                        'stage_name' => is_string($stageName) ? $stageName : $value,
                        'section' => $section
                    ];
                }
            } else {
                foreach ($stages as $stageName => $value) {
                    $flattened[] = [
                        'stage_name' => is_string($stageName) ? $stageName : $value,
                        'section' => $section
                    ];
                }
            }
        }

        return $flattened;
    }
}
