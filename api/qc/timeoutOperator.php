<?php
require_once __DIR__ . '/../header.php';



// Safely extract values
$id = $input['id'] ?? null;
$name = $input['name'] ?? null;
$time_out = date('Y-m-d H:i:s');
$model = $input['model'] ?? null;
$quantity = $input['quantity'] ?? null;
$good = $input['good'] ?? null;
$no_good = $input['nogood'] ?? null;
$replace = $input['replace'] ?? null;
$rework = $input['rework'] ?? null;
$total_quantity = $input['total_quantity'] ?? null;

$reference_no = $input['reference_no'] ?? null;
$material_description = $input['material_description'] ?? null;
$material_no = $input['material_no'] ?? null;
$shift = $input['shift'] ?? null; 
$lot_no = $input['lot_no'] ?? null; 
$date_needed = $input['date_needed'] ?? null; 
$pending_quantity = $input['pending_quantity'] ?? null; 



if ($pending_quantity>0){
$pending_quantity = $pending_quantity - $quantity;
}elseif($pending_quantity===null){
    $pending_quantity = $total_quantity - $quantity;
}

    try {
        // Begin transaction
        $db->beginTransaction();

        // Step 1: Update delivery_forms to set person_incharge_assembly
        $sqlUpdate = "UPDATE qc_list 
            SET 
            done_quantity = :quantity,
            pending_quantity = :pending_quantity,
            good = :good,
            no_good = :no_good,
            rework = :rework,
            `replace` = :replace,
            time_out = :time_out,
            person_incharge = :name
            WHERE id = :id";


        $paramsUpdate = [
            ':id' => $id,
            ':pending_quantity' => $pending_quantity,
            ':quantity' => $quantity,
            ':good' => $good,
            ':no_good' => $no_good,
            ':rework' => $rework,
            ':replace' => $replace,
            ':time_out'=>$time_out,
            ':name' => $name,

        ];
        $updated = $db->Update($sqlUpdate, $paramsUpdate);
        

        $newStatus = '';
        $newSection='';
         if ($reference_no) {
            try {
                // Query all done_quantity values for the given reference_no
                $sqlSum = "SELECT 
                        SUM(done_quantity) AS total_done, 
                        SUM(good) AS total_good,
                        SUM(no_good) AS total_no_good,
                        SUM(rework) AS total_rework,
                        SUM(`replace`) AS total_replace, 
                        MAX(total_quantity) AS total_required 
                    FROM qc_list 
                    WHERE reference_no = :reference_no";

                $paramsSum = [':reference_no' => $reference_no];
                
                $result = $db->SelectOne($sqlSum, $paramsSum);

                if ($result) {
                    $totalDone = (int)$result['total_done'];
                    $totalQuantity = (int)$result['total_required'];
                    $total_good = (int)$result['total_good'];
                    $total_no_good = (int)$result['total_no_good'];
                    $total_rework = (int)$result['total_rework'];
                    $total_replace = (int)$result['total_replace'];

                    if ($totalDone === $totalQuantity) {
                        
                        if($total_no_good>0){
                           $sqlInsert = "INSERT INTO rework_assembly
                        (itemID, model, material_no, material_description, shift, lot_no, `replace`, rework, quantity,assembly_quantity, date_needed, reference_no, created_at, status, section)
                        VALUES 
                        (:itemID, :model, :material_no, :material_description, :shift, :lot_no, :replace, :rework, :quantity,:assembly_quantity, :date_needed, :reference_no, :created_at, :status, :section)";

                            $paramsInsert = [
                                ':itemID'=>$id,
                                ':model'=>$model,
                                ':material_no' => $material_no,
                                ':material_description' => $material_description,
                                ':shift'=>$shift,
                                ':lot_no'=>$lot_no,
                                ':replace'=>$total_replace,
                                ':rework'=>$total_rework,
                                ':quantity'=>$total_no_good,
                                ':assembly_quantity'=>$total_no_good,
                                ':date_needed'=>$date_needed,
                                ':reference_no' => $reference_no,
                               
                                ':created_at'=>$time_out,
                                ':status'=> 'pending',
                                ':section'=>'assembly'
                            ];

                            $insertedId = $db->Insert($sqlInsert, $paramsInsert);

                            $newStatus='pending';
                            $newSection='rework';
                        }

                        $insertFGWarehouse = "INSERT INTO fg_warehouse (
                            reference_no, material_no,material_description,model,quantity,total_quantity,lot_no,shift,date_needed,section,status,created_at
                        ) VALUES (
                            :reference_no,:material_no,:material_description,:model,:quantity,:total_quantity,:lot_no,:shift,:date_needed,:section,:status,:created_at  
                        )";

                        $paramsFGWarehouse = [
                            ':reference_no' => $reference_no,
                            ':material_no' => $material_no,
                            ':material_description' => $material_description,
                            ':model' => $model,
                            ':quantity' => $total_good,
                            ':total_quantity' => $totalQuantity,
                            ':shift' => $shift,
                            ':lot_no' => $lot_no,
                            ':date_needed' => $date_needed,
                            ':status' => 'pending',
                            ':section' => 'warehouse',
                            ':created_at' => $time_out,
                        ];

                        $db->Insert($insertFGWarehouse, $paramsFGWarehouse);

                        if($no_good > 0){
                            $newStatus='pending';
                            $newSection='rework';
                        }else{
                            $newStatus='pending';
                            $newSection='warehouse';
                        }
                        
                        

                    
                        $sqlUpdateDelivery = "UPDATE delivery_form_new 
                                            SET section = :newSection
                                            WHERE reference_no = :reference_no";

                        $paramsUpdateDelivery = [':reference_no' => $reference_no,':newSection'=>$newSection];
                        $db->Update($sqlUpdateDelivery, $paramsUpdateDelivery);
                        
                        $sqlUpdateAssembly = "UPDATE assembly_list_new 
                                            SET status = :newStatus, section = :newSection
                                            WHERE reference_no = :reference_no";

                        $paramsUpdateAssembly = [':reference_no' => $reference_no,':newSection'=>$newSection,':newStatus'=>$newStatus];
                        $db->Update($sqlUpdateAssembly, $paramsUpdateAssembly);

                        $sqlUpdateQC = "UPDATE qc_list 
                                            SET status = :newStatus, section = :newSection
                                            WHERE reference_no = :reference_no";

                        $paramsUpdateQC = [':reference_no' => $reference_no,':newSection'=>$newSection,':newStatus'=>'done'];
                        $db->Update($sqlUpdateQC, $paramsUpdateQC);
                    } else{

                        $selectSql = "SELECT * FROM qc_list WHERE id = :id";
                        $selectParams = [':id' => $id];

                        $modifyCallback = function($row) use ($id,$pending_quantity,$time_out) {
                            return [
                                'itemID' =>$id,
                                'model' => $row['model'],
                                'material_no' => $row['material_no'],
                                'material_description' => $row['material_description'],
                                'reference_no'=> $row['reference_no'],
                                'shift'=> $row['shift'],
                                'lot_no' => $row['lot_no'],
                                'pending_quantity'=>  $pending_quantity,
                                'total_quantity'=>  $row['total_quantity'],
                                'status' => $row['status'],
                                'section' => $row['section'],
                                'date_needed'=> $row['date_needed'],
                                'created_at' => $time_out,

                            ];
                        };
                                $insertSql = "INSERT INTO qc_list ( itemID, model, material_no, material_description,pending_quantity, reference_no, shift, lot_no, total_quantity, status, section, date_needed, created_at ) 
                                VALUES ( :itemID, :model, :material_no, :material_description,:pending_quantity, :reference_no, :shift, :lot_no, :total_quantity, :status, :section, :date_needed, :created_at )";
                        
                        $db->DuplicateAndModify($selectSql, $selectParams, $modifyCallback, $insertSql);

                    }

              
                } else {
                    echo json_encode(['success' => false, 'message' => 'No data found for that reference number.']);
                }

            } catch (PDOException $e) {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'DB Error: ' . $e->getMessage()]);
            }

        $db->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Update and insert successful. Inventory updated.',
            'Pending Quantity'=>$pending_quantity
        ]);
    }


    } catch (PDOException $e) {
        $db->rollBack();
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'DB Error: ' . $e->getMessage()]);
    } catch (Exception $e) {
        $db->rollBack();
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }

