<?php
require_once __DIR__ . '/../header.php';


$model = new UserModel($db);

$id = $input['id'] ?? null;

if (!$id) {
    echo json_encode(['success' => false, 'message' => 'User ID is required for deletion.']);
    exit;
}
try {
    $user = $model->getUserById($id);
    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'User not found.']);
        exit;
    }

    $deleted = $model->deleteUser($id);

    if ($deleted) {
        echo json_encode(['success' => true, 'message' => 'User deleted successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete user.']);
    }

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
