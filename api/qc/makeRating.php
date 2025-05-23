<?php
// Show errors (for debugging)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include Composer autoloader and environment
require_once __DIR__ . '/../../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../env');
$dotenv->load();

require_once __DIR__ . '/../../Classes/Database/DatabaseClass.php';
$db = new DatabaseClass();

// Read POST body and decode JSON
$input = json_decode(file_get_contents("php://input"), true);

// Validate inputs
if (
    !isset($input['id'], $input['good'], $input['not_good'], $input['total_qty']) ||
    !is_numeric($input['id']) ||
    !is_numeric($input['good']) ||
    !is_numeric($input['not_good']) ||
    !is_numeric($input['total_qty'])
) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit;
}

$id = (int)$input['id'];
$good = (int)$input['good'];
$notGood = (int)$input['not_good'];
$totalQty = (int)$input['total_qty'];

try {
    $sql = "UPDATE assembly_list 
            SET good = :good, not_good = :not_good, status_qc = 'done'
            WHERE id = :id";

    $params = [
        ':good' => $good,
        ':not_good' => $notGood,
        ':id' => $id
    ];

    $result = $db->Update($sql, $params);

    echo json_encode(['success' => true, 'message' => 'Record updated successfully']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'DB Error: ' . $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
