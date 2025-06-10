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


$input = json_decode(file_get_contents('php://input'), true);

$id = $input['id'] ?? null;
$full_name = $input['full_name'] ?? null;
$time_out = date('Y-m-d H:i:s');
$inputQty = $input['inputQty'] ?? null;
$replace = $input['replace'] ?? null;
$rework = $input['rework'] ?? null;
$reference_no = $input['reference_no'] ?? null;
$quantity = $input['quantity'] ?? null;

$assembly_pending_quantity= $input['assembly_pending_quantity'] ?? null;
if($assembly_pending_quantity===null){
    $assembly_pending_quantity = $quantity - $inputQty;
}else{
$assembly_pending_quantity = $assembly_pending_quantity - $inputQty;
}


if (!$id || !$full_name) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing required data.']);
    exit;
}

try {
    $db->beginTransaction();

    $sqlUpdate = "UPDATE rework_assembly 
        SET assembly_person_incharge = :full_name, 
            `replace` = :replace, rework = :rework,
            assembly_pending_quantity = :assembly_pending_quantity,
            assembly_timeout = :time_out 
        WHERE id = :id";

    $paramsUpdate = [
        ':full_name' => $full_name,
        ':id' => $id,
        ':time_out' => $time_out,
        ':replace' => $replace,
        ':rework' => $rework,
        ':assembly_pending_quantity' => $assembly_pending_quantity
    ];

    $updated = $db->Update($sqlUpdate, $paramsUpdate);

    $insertedCount = 0; // Default in case no duplication occurs

    if ($reference_no) {
        $sqlSum = "SELECT 
            model,
            material_no,
            material_description,
            shift,
            lot_no,
            date_needed,
            SUM(`replace`) AS total_replace,
            SUM(`rework`) AS total_rework,
            SUM(`assembly_pending_quantity`) AS total_assembly_pending_quantity,
            MAX(quantity) AS total_quantity
            FROM rework_assembly
            WHERE reference_no = :reference_no
            GROUP BY reference_no, model, material_no, material_description, shift, lot_no, date_needed";

        $paramsSum = [':reference_no' => $reference_no];
        $result = $db->SelectOne($sqlSum, $paramsSum);

        if ($result) {
            $total_rework = (int)$result['total_rework'];
            $total_replace = (int)$result['total_replace'];
            $total = $total_rework + $total_replace;
            $total_quantity = (int)$result['total_quantity'];
            $material_no = $result['material_no'];
            if ($total === $total_quantity) {

                if ($material_no && $total_replace >0) {
                    $sqlComponents = "SELECT components_name, usage_type, actual_inventory 
                                    FROM components_inventory 
                                    WHERE material_no = :material_no";
                    $components = $db->Select($sqlComponents, [':material_no' => $material_no]);

                    if (!$components) {
                        throw new Exception("No components found for material_no: $material_no");
                    }

                    foreach ($components as $component) {
                        $componentsName = $component['components_name'];
                        $usageType = (int)$component['usage_type'];
                        $currentInventory = (int)$component['actual_inventory'];

                        // Calculate amount to add back
                        $returnQty = $total_replace * $usageType;
                        $newInventory = $currentInventory - $returnQty;

                        // Update inventory by adding back rework quantity
                        $sqlUpdateInventory = "UPDATE components_inventory 
                                            SET actual_inventory = :new_inventory 
                                            WHERE material_no = :material_no AND components_name = :components_name";

                        $paramsUpdateInventory = [
                            ':new_inventory' => $newInventory,
                            ':material_no' => $material_no,
                            ':components_name' => $componentsName,
                        ];

                        $db->Update($sqlUpdateInventory, $paramsUpdateInventory);
                    }
                }


                 $updateAssembly = "UPDATE rework_assembly 
                    SET status='done' 
                    WHERE reference_no =:reference_no";

                $paramsUpdateAssembly = [
                    ':reference_no' => $reference_no,
             
                ];

                $updatedAssembly = $db->Update($updateAssembly,$paramsUpdateAssembly);

                $insertReworkQC = "INSERT INTO rework_qc (
                    reference_no, model, material_no, material_description,
                    shift, lot_no, quantity,
                    qc_quantity,  qc_person_incharge,
                    qc_timein, qc_timeout,
                    status, section, date_needed, created_at
                ) VALUES (
                    :reference_no, :model, :material_no, :material_description,
                    :shift, :lot_no, :quantity,
                    :qc_quantity,  :qc_person_incharge,
                    :qc_timein, :qc_timeout,
                    :status, :section, :date_needed, :created_at
                )";

                $paramsReworkQC = [
                    ':reference_no' => $reference_no,
                    ':model' => $result['model'],
                    ':material_no' => $result['material_no'],
                    ':material_description' => $result['material_description'],
                    ':shift' => $result['shift'],
                    ':lot_no' => $result['lot_no'],
                    ':quantity' => $total,
                    ':qc_quantity' => $total,
                    ':qc_person_incharge' => null,
                    ':qc_timein' => null,
                    ':qc_timeout' => null,
                    ':status' => 'pending',
                    ':section' => 'qc',
                    ':date_needed' => $result['date_needed'],
                    ':created_at' => $time_out,
                ];

                $db->Insert($insertReworkQC, $paramsReworkQC);



            } else {
                // Duplicate remaining assembly record
                $selectSql = "SELECT * FROM rework_assembly WHERE id = :id";
                $selectParams = [':id' => $id];

                $modifyCallback = function($row) use ($id, $replace, $rework, $inputQty, $time_out) {
                    return [
                        'itemID' => $id,
                        'reference_no' => $row['reference_no'],
                        'model' => $row['model'],
                        'material_no' => $row['material_no'],
                        'material_description' => $row['material_description'],
                        'shift' => $row['shift'],
                        'lot_no' => $row['lot_no'],
                        'replace' => null,
                        'rework' => null,
                        'quantity' => $row['quantity'],
                        'assembly_quantity' => $row['assembly_pending_quantity'],
                        'assembly_pending_quantity' => $row['assembly_pending_quantity'],
                        'assembly_person_incharge' => null,
                        'assembly_timein' => null,
                        'assembly_timeout' => null,
    
                        'status' => 'continue',
                        'section' => 'assembly',
                        'date_needed' => $row['date_needed'],
                        'created_at' => $time_out,
                    ];
                };

                $insertSql = "INSERT INTO rework_assembly (
                    itemID,
                    reference_no, model, material_no, material_description,
                    shift, lot_no, `replace`, rework, quantity,
                    assembly_quantity, assembly_pending_quantity, assembly_person_incharge,
                    assembly_timein, assembly_timeout,
                
                    status, section, date_needed, created_at
                ) VALUES (
                    :itemID, :reference_no, :model, :material_no, :material_description,
                    :shift, :lot_no, :replace, :rework, :quantity,
                    :assembly_quantity, :assembly_pending_quantity, :assembly_person_incharge,
                    :assembly_timein, :assembly_timeout,
               
                    :status, :section, :date_needed, :created_at
                )";

                $insertedCount = $db->DuplicateAndModify($selectSql, $selectParams, $modifyCallback, $insertSql);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'No data found for that reference number.']);
        }
    }

    $db->commit();

    echo json_encode([
        'success' => true,
        'message' => "Update and optional duplication completed successfully.",
        'insertedCount' => $insertedCount,
        'assembly' => $assembly_pending_quantity,
        'rework' => $rework,
        'replace' => $replace
    ]);
} catch (Exception $e) {
    $db->rollback();
    error_log("Error during duplication: " . $e->getMessage());

    echo json_encode([
        'success' => false,
        'message' => "Operation failed: " . $e->getMessage()
    ]);
}
