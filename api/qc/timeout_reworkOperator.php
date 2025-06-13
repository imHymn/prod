<?php
require_once __DIR__ . '/../header.php';



$id = $input['id'] ?? null;
$full_name = $input['full_name'] ?? null;
$time_out = date('Y-m-d H:i:s');
$inputQty = $input['inputQty'] ?? null;
$no_good = $input['no_good'] ?? null;
$good = $input['good'] ?? null;
$reference_no = $input['reference_no'] ?? null;
$quantity = $input['quantity'] ?? null;


$qc_pending_quantity= $input['qc_pending_quantity'] ?? null;
if($qc_pending_quantity===null){
    $qc_pending_quantity = $quantity - $inputQty;
}else{
$qc_pending_quantity = $qc_pending_quantity - $inputQty;
}

if (!$id || !$full_name) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing required data.']);
    exit;
}

try {
    $db->beginTransaction();

    $sqlUpdate = "UPDATE rework_qc 
        SET qc_person_incharge = :full_name, 
            no_good = :no_good, good = :good,
            qc_pending_quantity = :qc_pending_quantity,
            qc_timeout = :time_out 
        WHERE id = :id";

    $paramsUpdate = [
        ':full_name' => $full_name,
        ':id' => $id,
        ':time_out' => $time_out,
        ':no_good' => $no_good,
        ':good' => $good,
        ':qc_pending_quantity' => $qc_pending_quantity
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
            SUM(`no_good`) AS total_noGood,
            SUM(`good`) AS total_good,
            SUM(`qc_pending_quantity`) AS total_qc_pending_quantity,
            MAX(quantity) AS total_quantity
            FROM rework_qc
            WHERE reference_no = :reference_no
            GROUP BY reference_no, model, material_no, material_description, shift, lot_no, date_needed";

        $paramsSum = [':reference_no' => $reference_no];
        $result = $db->SelectOne($sqlSum, $paramsSum);

        if ($result) {
            $total_good = (int)$result['total_good'];
            $total_noGood = (int)$result['total_noGood'];
            $total = $total_good + $total_noGood;
            $total_quantity = (int)$result['total_quantity'];

            if ($total === $total_quantity) {
                 $updateAssembly = "UPDATE rework_qc 
                    SET status='done' 
                    WHERE reference_no =:reference_no";
                $paramsUpdateAssembly = [
                    ':reference_no' => $reference_no,
             
                ];
                $updatedAssembly = $db->Update($updateAssembly,$paramsUpdateAssembly);

                $sqlUpdateDelivery = "UPDATE delivery_form_new 
                                        SET section = :section
                                        WHERE reference_no = :reference_no";

                $paramsUpdateDelivery = [':reference_no' => $reference_no,':section'=>'WAREHOUSE'];
                $db->Update($sqlUpdateDelivery, $paramsUpdateDelivery);

                $updateFGWarehouse = "UPDATE fg_warehouse SET  quantity = quantity + :total_good WHERE reference_no=:reference_no";

                $paramsFGWarehouse = [
                    ':reference_no' => $reference_no,
                    ':total_good' => $total_good,
                ];

                $db->Update($updateFGWarehouse, $paramsFGWarehouse);

            } else {
                // Duplicate remaining qc record
                $selectSql = "SELECT * FROM rework_qc WHERE id = :id";
                $selectParams = [':id' => $id];

                $modifyCallback = function($row) use ($id, $no_good, $good, $inputQty, $time_out) {
                    return [
                        'itemID' => $id,
                        'reference_no' => $row['reference_no'],
                        'model' => $row['model'],
                        'material_no' => $row['material_no'],
                        'material_description' => $row['material_description'],
                        'shift' => $row['shift'],
                        'lot_no' => $row['lot_no'],
                        'no_good' => null,
                        'good' => null,
                        'quantity' => $row['quantity'],
                        'qc_quantity' => $row['qc_pending_quantity'],
                        'qc_pending_quantity' => $row['qc_pending_quantity'],
                        'qc_person_incharge' => null,
                        'qc_timein' => null,
                        'qc_timeout' => null,
    
                        'status' => 'continue',
                        'section' => 'qc',
                        'date_needed' => $row['date_needed'],
                        'created_at' => $time_out,
                    ];
                };

                $insertSql = "INSERT INTO rework_qc (
                    itemID,
                    reference_no, model, material_no, material_description,
                    shift, lot_no, no_good, good, quantity,
                    qc_quantity, qc_pending_quantity, qc_person_incharge,
                    qc_timein, qc_timeout,
                
                    status, section, date_needed, created_at
                ) VALUES (
                    :itemID, :reference_no, :model, :material_no, :material_description,
                    :shift, :lot_no, :no_good, :good, :quantity,
                    :qc_quantity, :qc_pending_quantity, :qc_person_incharge,
                    :qc_timein, :qc_timeout,
               
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

    ]);
} catch (Exception $e) {
    $db->rollback();
    error_log("Error during duplication: " . $e->getMessage());

    echo json_encode([
        'success' => false,
        'message' => "Operation failed: " . $e->getMessage()
    ]);
}
