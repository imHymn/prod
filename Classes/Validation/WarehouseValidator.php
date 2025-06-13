<?php

namespace Validation;

class WarehouseValidator
{
    public static function validatePulledOutWarehouse(array $data): array
    {
        $errors = [];

        if (empty($data['id'])) {
            $errors['id'] = 'ID is required.';
        }

        if (empty($data['material_no'])) {
            $errors['material_no'] = 'Material number is required.';
        }

        if (empty($data['material_description'])) {
            $errors['material_description'] = 'Material description is required.';
        }

        if (!isset($data['total_quantity']) || !is_numeric($data['total_quantity']) || $data['total_quantity'] <= 0) {
            $errors['total_quantity'] = 'Total quantity must be a positive number.';
        }

        if (empty($data['reference_no'])) {
            $errors['reference_no'] = 'Reference number is required.';
        }

        return $errors;
    }
}
