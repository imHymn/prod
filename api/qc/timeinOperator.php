<?php
require_once __DIR__ . '/../header.php';

use Model\QCModel;
use Validation\QCValidator;

$id = $input['id'] ?? null;
$name = $input['name'] ?? null;
$time_in = date('Y-m-d H:i:s');

$errors = QCValidator::verifyQCPersonInCharge($id, $name, $time_in);
if (!empty($errors)) {
    echo json_encode([
        'success' => false,
        'message' => $errors
    ]);
    exit;
}

try {
    $qcModel = new QCModel($db);

    $db->beginTransaction();

    $qcModel->updateQCPersonInCharge($id, $name, $time_in);

    $db->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Update and insert successful. Inventory updated.',
    ]);
} catch (PDOException $e) {
    $db->rollBack();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'DB Error: ' . $e->getMessage()]);
} catch (Exception $e) {
    $db->rollBack();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
