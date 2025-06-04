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



// Sanitize and assign inputs
$id = (int)$input['id'];
$good = (int)$input['good'];
$notGood = (int)$input['not_good'];
$totalQty = (int)$input['total_qty'];
$oldGood = (int)$input['prev_good'];
$name = trim($input['name']);
$sumGood = $good + $oldGood;

// Optional values
$materialNo = $input['material_no'] ?? null;
$materialDescription = $input['material_description'] ?? null;
$model = $input['model'] ?? null;
$shift = $input['shift'] ?? null;
$lotNo = $input['lot_no'] ?? null;
$personInCharge = $input['person_incharge'] ?? null;
$dateNeeded = $input['date_needed'] ?? null;
$rework = $input['rework'] ?? null;
$replace = $input['replace'] ?? null;

$timeout = date('Y-m-d H:i:s');


try {
    // Update delivery_forms
    $sqlDelivery = "UPDATE delivery_forms 
        SET section = 'WAREHOUSE', status = 'pending', person_incharge_qc = :name 
        WHERE id = :id";
    $paramsDelivery = [
        ':id' => $id,
        ':name' => $name
    ];
    $db->Update($sqlDelivery, $paramsDelivery);

    // Update assembly_list
    $sqlAssembly = "UPDATE assembly_list 
        SET curr_section = 'WAREHOUSE', status_qc = 'done', good = :good, not_good = :not_good, 
            status_qc = 'done', rework_incharge_qc = :name ,rework_timeout_qc=:timeout
        WHERE id = :id";
    $paramsAssembly = [
        ':id' => $id,
        ':name' => $name,
        ':timeout'=>$timeout,
        ':good' => $sumGood,
        ':not_good' => $notGood
    ];
    $db->Update($sqlAssembly, $paramsAssembly);

    // Update warehouse
    $sqlWarehouse = "UPDATE fg_warehouse 
        SET good = :good, section = 'WAREHOUSE' 
        WHERE id = :id";
    $paramsWarehouse = [
        ':id' => $id,
        ':good' => $sumGood
    ];
    $db->Update($sqlWarehouse, $paramsWarehouse);
    
  

    echo json_encode(['success' => true, 'message' => 'Records updated successfully']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database update failed', 'error' => $e->getMessage()]);
}
