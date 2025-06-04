<?php
// Show errors (for debugging)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');
// Include Composer autoloader
require_once __DIR__ . '/../../vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../env');
$dotenv->load();

require_once __DIR__ . '/../../Classes/Database/DatabaseClass.php';
$db = new DatabaseClass();

// 🔄 Read JSON input
$input = json_decode(file_get_contents('php://input'), true);


$id = $input['id'] ?? null;
$itemID = $input['itemID'] ?? null;
$full_name = $input['full_name'] ?? null;
$reference_no = $input['reference_no'] ?? null;
$material_no = $input['material_no'] ?? null;
$material_description = $input['material_description'] ?? null;
$done_quantity = $input['done_quantity'] ?? null;

$total_qty = $input['total_qty'] ?? null;

$model = $input['model'] ?? null; 
$shift = $input['shift'] ?? null; 
$lot_no = $input['lot_no'] ?? null; 
$date_needed = $input['date_needed'] ?? null; 
$inputQty = $input['inputQty'] ?? null; 
$totalDone = null;
$result = null;

$pending_quantity = $input['pending_quantity'] ?? null;
$pending_quantity = $pending_quantity - $inputQty ;
$time_out = date('Y-m-d H:i:s');
if($pending_quantity > 0 ){
    $status_assembly='pending';
    $section_assembly='assembly';
}else{
    $status_assembly='done';
    $section_assembly='qc';
}
    try {
        // Begin transaction
        $db->beginTransaction();
        $sqlUpdate = "UPDATE assembly_list_new SET 
                    done_quantity=:done_quantity,
                    pending_quantity=:pending_quantity,
                    status=:status,
                    section=:section,
                    time_out = :time_out
                    WHERE itemID = :itemID";

                $paramsUpdate = [
                    ':done_quantity' =>$done_quantity,
                    ':pending_quantity' =>$pending_quantity,
                    ':itemID' => $itemID,
                    ':time_out' => $time_out,
                    ':status'=> $status_assembly,
                    ':section'=>$section_assembly
                ];

                $updated = $db->Update($sqlUpdate, $paramsUpdate);

                $sqlGetPending = "SELECT assembly_pending, total_quantity
                  FROM delivery_form_new 
                  WHERE id = :id 
                  ORDER BY created_at DESC 
                  LIMIT 1";

                $paramsGetPending = [':id' => $id];
                $currentPending = $db->SelectOne($sqlGetPending, $paramsGetPending);

                // Step 2: Determine starting value (assembly_pending or fallback to total_quantity)
                $basePending = isset($currentPending['assembly_pending']) && $currentPending['assembly_pending'] !== null
                    ? (int)$currentPending['assembly_pending']
                    : (int)$currentPending['total_quantity'];

                // Step 3: Subtract inputQty
                $remainingPending = $basePending - (int)$inputQty;

                // Optional: Prevent negative value
                $remainingPending = max(0, $remainingPending);

                // Step 4: Continue with update
                $sqlInsertDelivery = "UPDATE delivery_form_new SET 
                                        section = :section,
                                        status = :status,
                                        assembly_pending = :remainingPending - assembly_pending
                                    WHERE id = :id";

                $paramsInsertDelivery = [
                    ':section'   => 'QC',
                    ':status'    => 'done',
                    ':id'        => $id,
            
                    ':remainingPending' =>$remainingPending
                ];
                $insertDel = $db->Update($sqlInsertDelivery, $paramsInsertDelivery);


        // if($done_quantity === $total_qty){}

        if ($reference_no) {
            try {
                // Query all done_quantity values for the given reference_no
                $sqlSum = "SELECT SUM(done_quantity) AS total_done, MAX(total_quantity) AS total_required 
                        FROM assembly_list_new 
                        WHERE reference_no = :reference_no";
                $paramsSum = [':reference_no' => $reference_no];
                
                $result = $db->SelectOne($sqlSum, $paramsSum);

                if ($result) {
                    $totalDone = (int)$result['total_done'];
                    $totalQuantity = (int)$result['total_required'];

                    if ($totalDone === $totalQuantity) {
                        
                            $sqlInsert = "INSERT INTO qc_list
                            (model,shift,lot_no,date_needed, reference_no, material_no,material_description, total_quantity,status,section,created_at)
                            VALUES 
                            (:model,:shift,:lot_no,:date_needed, :reference_no, :material_no,:material_description, :total_quantity, :status,:section,:created_at)";
                        $paramsInsert = [
                        
                            ':model'=>$model,
                            ':shift'=>$shift,
                            ':lot_no'=>$lot_no,
                            ':date_needed'=>$date_needed,
                            ':reference_no' => $reference_no,
                            ':material_no' => $material_no,
                            ':material_description' => $material_description,
                            ':total_quantity' => $total_qty,
                            ':created_at'=>$time_out,
                            ':status'=> 'pending',
                            ':section'=>'qc'
                        ];

                        $insertedId = $db->Insert($sqlInsert, $paramsInsert);

                        // ✅ Update all items with the same reference_no to 'done' and section 'qc'
                        $sqlUpdateDelivery = "UPDATE delivery_form_new 
                                            SET section = 'QC' 
                                            WHERE reference_no = :reference_no";

                        $paramsUpdateDelivery = [':reference_no' => $reference_no];
                        $db->Update($sqlUpdateDelivery, $paramsUpdateDelivery);
                        
                        $sqlUpdateAssembly = "UPDATE assembly_list_new 
                                            SET status = 'done', section = 'qc' 
                                            WHERE reference_no = :reference_no";

                        $paramsUpdateAssembly = [':reference_no' => $reference_no];
                        $db->Update($sqlUpdateAssembly, $paramsUpdateAssembly);
                    } else{

                        $selectSql = "SELECT * FROM delivery_form_new WHERE id = :id";
                        $selectParams = [':id' => $id];

                        $modifyCallback = function($row) use ($time_out, $remainingPending) {
                            return [
                                'material_no' => $row['material_no'],
                                'material_description' => $row['material_description'],
                                'model_name' => $row['model_name'],
                                'quantity'=> $row['quantity'],
                                'total_quantity'=> $row['total_quantity'],
                                'supplement_order'=> $row['supplement_order'],
                                'date_needed'=> $row['date_needed'],
                                'lot_no' => $row['lot_no'],
                                'reference_no'=> $row['reference_no'],
                                'shift'=> $row['shift'],
                                'status' => 'continue',
                                'section' => 'ASSEMBLY',
                                'assembly_pending' => $remainingPending,
                                'created_at' => $time_out,
                            ];
                        };

                                $insertSql = "INSERT INTO delivery_form_new ( reference_no, material_no, material_description, model_name, quantity, total_quantity,assembly_pending, supplement_order, date_needed, lot_no, shift, status, section, created_at ) 
                                VALUES ( :reference_no, :material_no, :material_description, :model_name, :quantity, :total_quantity,:assembly_pending, :supplement_order, :date_needed, :lot_no, :shift, :status, :section, :created_at )";
                            
                         
                            $db->DuplicateAndModify($selectSql, $selectParams, $modifyCallback, $insertSql);
                        

                    }

              
                } else {
                    echo json_encode(['success' => false, 'message' => 'No data found for that reference number.']);
                }

            } catch (PDOException $e) {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'DB Error: ' . $e->getMessage()]);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'reference_no is required.']);
        }

echo json_encode([
    'success' => true,
    'reference_no' => $reference_no,
    'done_quantity' => $totalDone,
    'message' => 'Update and insert successful',
    'result' => $result,
    'id'=>$id,
    $pending_quantity
]);

  $db->commit();
    } catch (PDOException $e) {
        $db->rollBack();
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'DB Error: ' . $e->getMessage()]);
    } catch (Exception $e) {
        $db->rollBack();
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }

