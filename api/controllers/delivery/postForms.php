<?php
session_start();
require_once __DIR__ . '/../../../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../../');
$dotenv->load();

require_once __DIR__ . '/../../../Classes/Database/DatabaseClass.php';
$db = new DatabaseClass();
date_default_timezone_set('Asia/Manila');

header('Content-Type: application/json');
$input = json_decode(file_get_contents('php://input'), true);



if (!is_array($input)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid JSON input']);
    exit;
}

$insertedCount = 0;
$currentDateTime = date('Y-m-d H:i:s'); // Get current datetime


// foreach ($lot_no as $row) {


// }

$model_name = $input[0]['model_name'] ?? null;
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
$insufficientStockItems = [];

foreach ($input as $item) {
    if (empty($item['material_no']) || empty($item['material_description'])) {
        continue;
    }

    // Step 1: Check inventory availability
    $invCheckSql = "SELECT quantity FROM material_inventory 
                    WHERE material_no = :material_no 
                      AND material_description = :material_description 
                      AND model_name = :model_name 
                    LIMIT 1";
    $invParams = [
        ':material_no' => $item['material_no'],
        ':material_description' => $item['material_description'],
        ':model_name' => $item['model_name']
    ];
    $inventory = $db->Select($invCheckSql, $invParams);

    if (empty($inventory)) {
        $insufficientStockItems[] = [
            'material_no' => $item['material_no'],
            'material_description' => $item['material_description'],
            'reason' => 'Material not found in inventory'
        ];
        continue;
    }

    $currentInventory = (int)$inventory[0]['quantity'];
    $requiredQty = (int)$item['total_quantity'];

    if ($currentInventory < $requiredQty) {
        $insufficientStockItems[] = [
            'material_no' => $item['material_no'],
            'material_description' => $item['material_description'],
            'reason' => "Insufficient stock (Available: $currentInventory, Needed: $requiredQty)"
        ];
    }
}

// If any items failed the inventory check, return error
if (!empty($insufficientStockItems)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Some items do not have enough stock in inventory.',
        'insufficient_items' => $insufficientStockItems
    ]);
    exit;
}

$today = date('Ymd');

// Get latest reference_no for today
$refCheckSql = "SELECT reference_no FROM delivery_forms 
                WHERE reference_no LIKE :today_pattern 
                ORDER BY reference_no DESC 
                LIMIT 1";
$refCheckParams = [':today_pattern' => $today . '-%'];
$refResult = $db->Select($refCheckSql, $refCheckParams);

if (!empty($refResult)) {
    $lastRef = $refResult[0]['reference_no'];
    $lastNumber = (int)substr($lastRef, -4); // Get last 4 digits
} else {
    $lastNumber = 0;
}

foreach ($input as $item) {
    // Increase reference number per row
    $lastNumber++;
    $reference_no = $today . '-' . str_pad($lastNumber, 4, '0', STR_PAD_LEFT);

    // Re-check inventory per item
    $invCheckSql = "SELECT quantity FROM material_inventory 
                    WHERE material_no = :material_no 
                      AND material_description = :material_description 
                      AND model_name = :model_name 
                    LIMIT 1";
    $invParams = [
        ':material_no' => $item['material_no'],
        ':material_description' => $item['material_description'],
        ':model_name' => $item['model_name']
    ];
    $inventory = $db->Select($invCheckSql, $invParams);

    if (empty($inventory)) {
        continue;
    }

    $currentInventory = (int)$inventory[0]['quantity'];
    $requiredQty = (int)$item['total_quantity'];
    $newQty = $currentInventory - $requiredQty;

    $sql = "INSERT INTO delivery_form_new
        (reference_no, model_name, material_no, material_description, quantity, supplement_order, total_quantity, status, section, shift, lot_no, created_at, updated_at, date_needed)
        VALUES
        (:reference_no, :model_name, :material_no, :material_description, :quantity, :supplement_order, :total_quantity, :status, :section, :shift, :lot_no, :created_at, :updated_at, :date_needed)";

    $params = [
        ':reference_no' => $reference_no,
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
        ':date_needed' => $item['date_needed']
    ];

    $result = $db->Insert($sql, $params);

    if ($result !== false) {
        $insertedCount++;

        // Update inventory with new quantity
        $updateSql = "UPDATE material_inventory 
                      SET quantity = :new_quantity 
                    WHERE material_no = :material_no 
                      AND material_description = :material_description 
                      AND model_name = :model_name";
        $updateParams = [
            ':new_quantity' => $newQty,
            ':material_no' => $item['material_no'],
            ':material_description' => $item['material_description'],
            ':model_name' => $item['model_name']
        ];
        $db->Update($updateSql, $updateParams);
    }
}

echo json_encode([
    'val' => $lot_value,
    'status' => 'success',
    'inserted' => $insertedCount,
    'received' => count($input),
]);

