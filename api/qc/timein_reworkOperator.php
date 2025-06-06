<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

require_once __DIR__ . '/../../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../env');
$dotenv->load();

require_once __DIR__ . '/../../Classes/Database/DatabaseClass.php';
$db = new DatabaseClass();
date_default_timezone_set('Asia/Manila');

$input = json_decode(file_get_contents('php://input'), true);

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

    $sqlUpdate = "UPDATE rework_qc
                  SET qc_person_incharge = :full_name, 
                      qc_timein = :time_in 
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
