<?php
require_once __DIR__ . '/../header.php';

use Model\AccountModel;

$model = new AccountModel($db);

try {
    $users = $model->getAllUsers();
    echo json_encode($users);
} catch (PDOException $e) {
    echo json_encode(['error' => 'DB Error', 'message' => $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['error' => 'Error', 'message' => $e->getMessage()]);
}
