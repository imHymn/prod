<?php
// Show errors (for debugging)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include Composer autoloader
require_once __DIR__ . '/../../vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../env');
$dotenv->load();

require_once __DIR__ . '/../../Classes/Database/DatabaseClass.php';
$db = new DatabaseClass();

// Read JSON input from request body
$input = json_decode(file_get_contents('php://input'), true);
$id = $input['id'] ?? null;

header('Content-Type: application/json');

if (!$id) {
    echo json_encode(['success' => false, 'message' => 'Missing id']);
    exit;
}

try {
    // Update warehouse
    $sqlWarehouse = "UPDATE warehouse SET status = 'DONE' WHERE id = :id";
    $params = [':id' => $id];
    $updatedWarehouse = $db->Update($sqlWarehouse, $params);

    // Update delivery_forms
    $sqlDelivery = "UPDATE delivery_forms SET status = 'DONE', section = 'DELIVERY' WHERE id = :id";
    $updatedDelivery = $db->Update($sqlDelivery, $params);

    if ($updatedWarehouse || $updatedDelivery) {
        echo json_encode(['success' => true, 'message' => 'Item marked as PULLED OUT']);
    } else {
        echo json_encode(['success' => false, 'message' => 'No records were updated']);
    }

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'DB Error: ' . $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
