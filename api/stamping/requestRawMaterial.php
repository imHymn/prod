<?php
require_once __DIR__ . '/../header.php';



try {
    $inputJSON = file_get_contents('php://input');
    $input = json_decode($inputJSON, true);

    if (!$input) {
        throw new Exception("Invalid JSON input");
    }

    $material_no = $input['material_no'] ?? null;
    $component_name = $input['component_name'] ?? null;
    $usage = $input['usage'] ?? null;
    $quantity = 300 * $usage;
    $process_quantity = $input['process_quantity'] ?? null;

    if (!$material_no || !$component_name || !$quantity) {
        throw new Exception("Missing required fields: material_no, component_name, or quantity");
    }

   
    $checkSql = "SELECT COUNT(*) as count FROM `pending_rmwarehouse` 
                 WHERE material_no = :material_no 
                 AND material_description = :component_name 
                 AND status = 'pending'";
                 
    $checkParams = [
        ':material_no' => $material_no,
        ':component_name' => $component_name
    ];

    $existing = $db->Select($checkSql, $checkParams);

    if ($existing && $existing[0]['count'] > 0) {
        echo json_encode([
            'status' => 'exists',
            'message' => 'A pending request already exists for this material.'
        ]);
        exit;
    }

 
    $insertSql = "INSERT INTO `pending_rmwarehouse` 
                  (material_no, material_description,process_quantity, quantity, status, section) 
                  VALUES (:material_no, :component_name,:process_quantity, :quantity, :status, :section)";
    
    $insertParams = [
        ':material_no' => $material_no,
        ':component_name' => $component_name,
        ':process_quantity'=>$process_quantity,
        ':quantity' => $quantity,
        ':status' => 'pending',
        ':section' => 'rm'
    ];

    $insertResult = $db->Insert($insertSql, $insertParams);

    
$updateSql = "UPDATE components_inventory 
              SET status = :status, section = :section 
              WHERE material_no = :material_no";

$updateParams = [
    ':status' => 'pending',
    ':section' => 'rm',
    ':material_no' => $material_no
];

$updateResult = $db->Update($updateSql, $updateParams);


if ( $updateResult) {
    echo json_encode([
        'status' => 'success',
        'message' => 'Request inserted and inventory updated successfully'
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' =>  ' Update result: ' . json_encode($updateResult)
    ]);
}
exit;


} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => "DB Error: " . $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => "Error: " . $e->getMessage()]);
}
