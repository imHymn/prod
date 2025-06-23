<?php
require_once __DIR__ . '/../header.php';

use Model\AccountModel;
use Validation\AccountValidator;

$model = new AccountModel($db);

function trimOrNull($value)
{
    $trimmed = trim($value ?? '');
    return $trimmed === '' ? null : $trimmed;
}

$id = trimOrNull($input['id'] ?? null);
$data = [
    'name' => array_key_exists('name', $input) ? trimOrNull($input['name']) : null,
    'user_id' => null, // Not updatable
    'password' => array_key_exists('password', $input) ? $input['password'] : null,
    'production' => array_key_exists('production', $input) ? trimOrNull($input['production']) : null,
    'role' => array_key_exists('role', $input) ? trimOrNull($input['role']) : null,
    'production_location' => array_key_exists('production_location', $input) ? trimOrNull($input['production_location']) : null
];

if (empty($id)) {
    echo json_encode(['success' => false, 'message' => 'ID is required.']);
    exit;
}

$user = $model->getUserById((int)$id);
if (!$user) {
    echo json_encode(['success' => false, 'message' => 'User not found.']);
    exit;
}

$data = array_filter($data, fn($v) => $v !== null);


if (!empty($data['password'])) {
    $data['password'] = hash('sha512', $data['password']);
} else {
    unset($data['password']);
}

$errors = AccountValidator::validateUpdate(array_merge($user, $data));
if (!empty($errors)) {
    echo json_encode(['success' => false, 'message' => implode(' ', $errors)]);
    exit;
}

try {
    $updated = $model->updateUser((int)$id, array_merge($user, $data));

    if ($updated) {
        echo json_encode(['success' => true, 'message' => 'Account updated successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'No changes made or update failed.']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
