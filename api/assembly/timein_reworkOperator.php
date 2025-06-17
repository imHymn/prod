<?php
require_once __DIR__ . '/../header.php';
require_once __DIR__ . '/../header.php';

use Model\AssemblyModel;

$id = $input['id'] ?? null;
$full_name = $input['full_name'] ?? null;
$time_in = date('Y-m-d H:i:s');

if (!$id || !$full_name) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing required data.']);
    exit;
}

try {
    $db->beginTransaction();

    $assemblyModel = new AssemblyModel($db);
    $result = $assemblyModel->markReworkAssemblyTimeIn($id, $full_name, $time_in);

    if ($result !== true) {
        throw new Exception($result);
    }

    $db->commit();
    echo json_encode(['success' => true, 'message' => 'Update successful.']);
} catch (PDOException $e) {
    $db->rollBack();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'DB Error: ' . $e->getMessage()]);
} catch (Exception $e) {
    $db->rollBack();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => '❌ ' . $e->getMessage()]);
}
