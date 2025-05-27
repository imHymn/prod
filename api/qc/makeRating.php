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
    !isset($input['id'], $input['good'], $input['not_good'], $input['total_qty'], $input['name']) ||
    !is_numeric($input['id']) ||
    !is_numeric($input['good']) ||
    !is_numeric($input['not_good']) ||
    !is_numeric($input['total_qty']) ||
    empty(trim($input['name']))
) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit;
}

$id = (int)$input['id'];
$good = (int)$input['good'];
$notGood = (int)$input['not_good'];
$totalQty = (int)$input['total_qty'];
$name = trim($input['name']);
$materialNo = $input['material_no'] ?? null;
$materialDescription = $input['material_description'] ?? null;

$model = $input['model'] ?? null;
$shift = $input['shift'] ?? null;
$lotNo = $input['lot_no'] ?? null;
$personInCharge = $input['person_incharge'] ?? null;
$date_needed = $input['date_needed'] ?? null;
$rework = $input['rework'] ?? null;
$replace = $input['replace'] ?? null;
try {
    // Case 1: If there are NOT GOOD items
    if ($notGood > 0) {
    $sql = "UPDATE assembly_list 
        SET good = :good, not_good = :not_good, 
            status_qc = 'done', status_rework = 'pending', 
            curr_section = 'ASSEMBLY', person_incharge_qc = :qc_name,rework=:rework,`replace`=:replace
        WHERE id = :id";

$params = [
    ':rework' => $rework,
     ':replace' => $replace,
    ':good' => $good,
    ':not_good' => $notGood,
    ':qc_name' => $name,
    ':id' => $id
];
$db->Update($sql, $params);

         $sqlUpdate1 = "UPDATE delivery_forms 
        SET section='ASSEMBLY', status='pending', person_incharge_qc = :name
        WHERE id = :id";

    $paramsUpdate1 = [
        ':id' => $id,
        ':name' => $name
    ];

    $db->Update($sqlUpdate1, $paramsUpdate1);
    
     $status='pending';
    $section='assembly';
    // 2) Then INSERT into warehouse
    $sqlInsert = "INSERT INTO warehouse (
                    material_no, material_description, model,good, total_qty, lot_no, shift, date_needed, person_incharge,status,section
                ) VALUES (
                    :material_no, :material_description, :model,:good, :total_qty, :lot_no, :shift, :date_needed, :person_incharge,:status,:section
                )";

    $paramsInsert = [
        ':material_no' => $materialNo,
        ':material_description' => $materialDescription,
        ':model' => $model,
        ':good' => $good,
        ':total_qty' => $totalQty,
        ':lot_no' => $lotNo,
        ':shift' => $shift,
        ':date_needed' => $date_needed,
        ':person_incharge' => $name,
        ':status'=>$status,
        ':section'=>$section
    ];

    $db->Update($sqlInsert, $paramsInsert);

    echo json_encode(['success' => true, 'message' => 'Record updated and inserted successfully']);
    exit;  // Important to stop further code execution
    }else if ($good === $totalQty) {
    // 1) UPDATE assembly_list first
    $sqlUpdate = "UPDATE assembly_list 
                SET good = :good, not_good = :not_good, 
                    status_qc = 'done', status_rework = 'done', 
                    curr_section = 'WAREHOUSE', person_incharge_qc = :qc_name
                WHERE id = :id";

    $paramsUpdate = [
        ':good' => $good,
        ':not_good' => $notGood,
        ':qc_name' => $name,
        ':id' => $id
    ];

    $db->Update($sqlUpdate, $paramsUpdate);
    $sqlUpdate1 = "UPDATE delivery_forms 
        SET section='WAREHOUSE', status='pending', person_incharge_qc = :name
        WHERE id = :id";

    $paramsUpdate1 = [
        ':id' => $id,
        ':name' => $name
    ];

    $db->Update($sqlUpdate1, $paramsUpdate1);


    $status='pending';
    $section='warehouse';
    // 2) Then INSERT into warehouse
    $sqlInsert = "INSERT INTO warehouse (
                    material_no, material_description, model,good, total_qty, lot_no, shift, date_needed, person_incharge,status,section
                ) VALUES (
                    :material_no, :material_description, :model,:good, :total_qty, :lot_no, :shift, :date_needed, :person_incharge,:status,:section
                )";

    $paramsInsert = [
        ':material_no' => $materialNo,
        ':material_description' => $materialDescription,
        ':model' => $model,
        ':total_qty' => $totalQty,
        ':lot_no' => $lotNo,
        ':shift' => $shift,
        ':date_needed' => $date_needed,
        ':person_incharge' => $name,
        ':good'=>$good,
        ':status'=>$status,
        ':section'=>$section
    ];

    $db->Update($sqlInsert, $paramsInsert);

    echo json_encode(['success' => true, 'message' => 'Record updated and inserted successfully']);
    exit;  // Important to stop further code execution
}


    // Execute the query
    $result = $db->Update($sql, $params);

    echo json_encode(['success' => true, 'message' => 'Record updated successfully']);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'DB Error: ' . $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
