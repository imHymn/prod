<?php
require_once __DIR__ . '/../header.php';



$id = $input['id'] ?? null;
$full_name = $input['full_name'] ?? null;
$time_in = date('Y-m-d H:i:s');

// ✅ Validate required input
if (!$id || !$full_name) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing required data.']);
    exit;
}

try {
    $db->beginTransaction();

    $sqlUpdate = "UPDATE rework_assembly 
                  SET assembly_person_incharge = :full_name, 
                      assembly_timein = :time_in 
                  WHERE id = :id";

    $paramsUpdate = [
        ':full_name' => $full_name,
        ':id' => $id,
        ':time_in' => $time_in
    ];

    $updated = $db->Update($sqlUpdate, $paramsUpdate);

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
