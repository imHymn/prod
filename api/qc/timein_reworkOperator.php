<?php
require_once __DIR__ . '/../header.php';

use Model\QCModel;

$id = $input['id'] ?? null;
$full_name = $input['full_name'] ?? null;
$time_in = date('Y-m-d H:i:s');

try {
    $db->beginTransaction();

    $qcModel = new QCModel($db);
    $updated = $qcModel->updateReworkQCTimeIn((int)$id, $full_name, $time_in);

    $db->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Update successful.',
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
