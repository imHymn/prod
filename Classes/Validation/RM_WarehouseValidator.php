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
    public static function flattenStages($stage_name): array
    {
        if (is_string($stage_name)) {
            $stage_name = json_decode($stage_name, true);
        }

        if (!is_array($stage_name)) {
            throw new \InvalidArgumentException("Invalid stage_name format; expected array or valid JSON string.");
        }

        $flattened = [];
        foreach ($stage_name as $section => $stages) {
            foreach ($stages as $stage) {
                $flattened[] = [
                    'stage_name' => $stage,
                    'section' => $section
                ];
            }
        }
        return $flattened;
    }
}
