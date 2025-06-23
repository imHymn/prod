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

$data = [
    'name' => trimOrNull($input['name'] ?? null),
    'user_id' => trimOrNull($input['user_id'] ?? null),
    'password' => $input['password'] ?? null,
    'production' => trimOrNull($input['production'] ?? null),
    'role' => trimOrNull($input['role'] ?? null),
    'production_location' => trimOrNull($input['production_location'] ?? null),
    'created_at' => date('Y-m-d H:i:s')
];

$errors = AccountValidator::validateRegister($data);
if (!empty($errors)) {
    echo json_encode(['success' => false, 'message' => implode(' ', $errors)]);
    exit;
}

try {
    // 🔍 Check for duplicate user_id
    if ($model->getUserByUserId($data['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'User ID already in use.']);
        exit;
    }


    $data['password'] = hash('sha512', $data['password']);

    $inserted = $model->createUser($data);

    if ($inserted) {
        echo json_encode(['success' => true, 'message' => 'Account created successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Insert failed.']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
