<?php

namespace Validation;

class DeliveryValidator
{
    public static function validateModelName(?string $modelName): array
    {
        $errors = [];

        if (empty($modelName)) {
            $errors['model_name'] = 'Model name is required.';
        }

        return $errors;
    }
    public static function validateCustomerName(?string $customerName): array
    {
        $errors = [];

        if (empty($customerName)) {
            $errors['customer_name'] = 'Customer name is required.';
        }

        return $errors;
    }
    public static function validateSKU(?string $customerName, ?string $modelName): array
    {
        $errors = [];

        if (empty($customerName)) {
            $errors['customer_name'] = 'Customer name is required.';
        }
        if (empty($modelName)) {
            $errors['model_name'] = 'Model name is required.';
        }
        return $errors;
    }
    public static function validatePostForms(array $input): array
    {
        $errors = [];

        foreach ($input as $index => $item) {
            $lineErrors = [];

            if (empty($item['model_name'])) {
                $lineErrors['model_name'] = 'Model name is required.';
            }

            if (!isset($item['total_quantity']) || !is_numeric($item['total_quantity']) || $item['total_quantity'] <= 0) {
                $lineErrors['total_quantity'] = 'Total quantity must be a positive number.';
            }

            if (!empty($lineErrors)) {
                $errors[$index] = $lineErrors; // Add per row errors
            }
        }
        return $errors;
    }
}
