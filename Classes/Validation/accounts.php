<?php

class UserValidator {
    public static function validateRegister(array $data): array {
        $errors = [];

        if (empty($data['name'])) {
            $errors[] = 'Name is required.';
        }

        if (empty($data['user_id'])) {
            $errors[] = 'User ID is required.';
        }

        if (empty($data['password'])) {
            $errors[] = 'Password is required.';
        } elseif (strlen($data['password']) < 5) {
            $errors[] = 'Password must be at least 5 characters.';
        }
        if (empty($data['role'])) {
            $errors[] = 'Role is required.';
        }

        return $errors;
    }
    public static function validateUpdate(array $data): array {
        $errors = [];

        if (array_key_exists('name', $data) && empty($data['name'])) {
            $errors[] = 'Name cannot be empty.';
        }

        if (array_key_exists('password', $data)) {
            if (empty($data['password'])) {
                $errors[] = 'Password cannot be empty.';
            } elseif (strlen($data['password']) < 6) {
                $errors[] = 'Password must be at least 6 characters.';
            }
        }
        return $errors;
    }


    // ✅ Add this for login validation
    public static function validateLogin(array $data): array {
        $errors = [];

        if (empty($data['user_id'])) {
            $errors[] = 'User ID is required.';
        }

        if (empty($data['password'])) {
            $errors[] = 'Password is required.';
        }

        return $errors;
    }
}
