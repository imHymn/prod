<?php
require_once __DIR__ . '/../header.php';

use Model\AssemblyModel;
use Validation\AssemblyValidator;

$materialId = $input['materialId'];
$errors = AssemblyValidator::validateMaterialId($materialId);
if (!empty($errors)) {
    echo json_encode(['success' => false, 'errors' => $errors]);
    exit;
}

try {
    $model = new AssemblyModel($db);
    $users = $model->getSpecificComponent($materialId);

    echo json_encode($users);
} catch (PDOException $e) {
    echo "DB Error: " . $e->getMessage();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
