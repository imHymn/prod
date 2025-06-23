<?php

namespace Validation;

class QCValidator
{
    public static function verifyQCPersonInCharge($id, $name, $time_in): array
    {
        $errors = [];

        if (empty($id) || !is_numeric($id)) {
            $errors[] = 'Invalid or missing ID.';
        }

        if (empty($name) || !is_string($name)) {
            $errors[] = 'Name is required.';
        }

        if (empty($time_in) || !strtotime($time_in)) {
            $errors[] = 'Invalid time format.';
        }

        return $errors;
    }
    public static function validateTimeOutQC(array $data): array
    {
        $errors = [];

        if (empty($data['id']) || !is_numeric($data['id'])) {
            $errors[] = 'Invalid or missing ID.';
        }

        if (empty($data['name']) || !is_string($data['name'])) {
            $errors[] = 'Name is required.';
        }

        if (!strtotime($data['time_out'])) {
            $errors[] = 'Invalid or missing time_out.';
        }

        if (empty($data['model'])) {
            $errors[] = 'Model is required.';
        }

        foreach (['quantity', 'good', 'no_good', 'replace', 'rework', 'total_quantity'] as $field) {
            if (!isset($data[$field]) || !is_numeric($data[$field])) {
                $errors[] = "Invalid or missing {$field}.";
            }
        }

        if (empty($data['reference_no'])) {
            $errors[] = 'Reference No. is required.';
        }

        if (empty($data['material_description'])) {
            $errors[] = 'Material description is required.';
        }

        if (empty($data['material_no'])) {
            $errors[] = 'Material number is required.';
        }

        if (empty($data['shift'])) {
            $errors[] = 'Shift is required.';
        }

        if (empty($data['lot_no'])) {
            $errors[] = 'Lot No. is required.';
        }

        if (empty($data['date_needed']) || !strtotime($data['date_needed'])) {
            $errors[] = 'Invalid or missing date needed.';
        }

        return $errors;
    }
}
