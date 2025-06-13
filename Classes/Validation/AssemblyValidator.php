<?php

namespace Validation;

class AssemblyValidator
{
    public static function validateMaterialId(int $materialId): array
    {
        $errors = [];
        if (empty($materialId)) {
            $errors['model_name'] = 'Model name is required.';
        }
        return $errors;
    }
    public static function validateAssemblyDataTimeIn(array $data): array
    {
        $errors = [];
        $requiredFields = [
            'itemID',
            'model',
            'shift',
            'lot_no',
            'date_needed',
            'reference_no',
            'material_no',
            'material_description',
            'total_qty',
            'person_incharge',
            'time_in'
        ];

        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                $errors[$field] = "$field is required.";
            }
        }
        if (!empty($data['total_qty']) && (!is_numeric($data['total_qty']) || $data['total_qty'] < 0)) {
            $errors['total_qty'] = 'Total quantity must be a non-negative number.';
        }
        if (!empty($data['time_in']) && !preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $data['time_in'])) {
            $errors['time_in'] = 'Time in must be in the format YYYY-MM-DD HH:MM:SS.';
        }

        return $errors;
    }
    public static function validateAssemblyTimeOut(array $data): array
    {
        $errors = [];

        $requiredFields = [
            'id',
            'itemID',
            'full_name',
            'reference_no',
            'material_no',
            'material_description',
            'done_quantity',
            'total_qty',
            'model',
            'shift',
            'lot_no',
            'date_needed',
            'inputQty',
            'pending_quantity',
            'time_out'
        ];

        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || $data[$field] === '') {
                $errors[$field] = "$field is required.";
            }
        }

        if (!empty($data['done_quantity']) && (!is_numeric($data['done_quantity']) || $data['done_quantity'] < 0)) {
            $errors['done_quantity'] = 'Done quantity must be a non-negative number.';
        }

        if (!empty($data['inputQty']) && (!is_numeric($data['inputQty']) || $data['inputQty'] <= 0)) {
            $errors['inputQty'] = 'Input quantity must be a positive number.';
        }

        if (!empty($data['pending_quantity']) && !is_numeric($data['pending_quantity'])) {
            $errors['pending_quantity'] = 'Pending quantity must be a number.';
        }

        // Date format check
        if (!empty($data['time_out']) && !preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $data['time_out'])) {
            $errors['time_out'] = 'Time out must be in the format YYYY-MM-DD HH:MM:SS.';
        }

        return $errors;
    }
}
