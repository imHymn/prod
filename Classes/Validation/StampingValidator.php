<?php

namespace Validation;

class StampingValidator
{
    public static function validateSection(string $section): array
    {
        $errors = [];

        if (empty($section) || $section == null) {
            $errors['section'] = 'Section is required.';
        }


        return $errors;
    }
}
