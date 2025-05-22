<?php
// Show errors for debugging - remove or comment out in production
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

// Load Composer autoloader and dotenv
require_once __DIR__ . '/../../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../env');
$dotenv->load();

require_once __DIR__ . '/../../Classes/Database/DatabaseClass.php';

// Instantiate your database class
$db = new DatabaseClass();

// Read the raw POST body JSON
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!is_array($data)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid JSON input']);
    exit;
}

$insertedCount = 0;
$currentDateTime = date('Y-m-d H:i:s'); // Get current datetime


// foreach ($lot_no as $row) {


// }

$model_name = $data[0]['model_name'] ?? null;
if (!$model_name) {
    echo json_encode(['status' => 'error', 'message' => 'model_name missing']);
    exit;
}

$sql = "SELECT lot_no FROM delivery_forms WHERE model_name = :model_name ORDER BY lot_no DESC LIMIT 1";
$lot_rows = $db->Select($sql, [':model_name' => $model_name]);

if (count($lot_rows) > 0) {
    $lot_value = (int)$lot_rows[0]['lot_no'] + 1;
} else {
    $lot_value = 1; // default first lot_no if none exists
}

$insertedCount = 0;
$currentDateTime = date('Y-m-d H:i:s');

foreach ($data as $item) {
    // Validate required fields
    if (empty($item['material_no']) || empty($item['material_description'])) {
        continue;
    }

    $sql = "INSERT INTO delivery_forms
        (model_name, material_no, material_description, quantity, supplement_order, total_quantity, status, section, shift, lot_no, created_at, updated_at,date_needed)
        VALUES
        (:model_name, :material_no, :material_description, :quantity, :supplement_order, :total_quantity, :status, :section, :shift, :lot_no, :created_at, :updated_at,:date_needed)";

    $params = [
        ':model_name' => $item['model_name'] ?? '',
        ':material_no' => $item['material_no'],
        ':material_description' => $item['material_description'],
        ':quantity' => $item['quantity'] ?? 0,
        ':supplement_order' => is_numeric($item['supplement_order']) ? (int)$item['supplement_order'] : null,
        ':total_quantity' => $item['total_quantity'] ?? 0,
        ':status' => $item['status'] ?? '',
        ':section' => $item['section'] ?? '',
        ':shift' => $item['shift'] ?? '',
        ':lot_no' => $lot_value,
        ':created_at' => $currentDateTime,
        ':updated_at' => $currentDateTime,
    ];

    $result = $db->Insert($sql, $params);
    if ($result !== false) {
        $insertedCount++;
    }
}

echo json_encode([
    'val' => $lot_value,
    'status' => 'success',
    'inserted' => $insertedCount,
    'received' => count($data),
]);
