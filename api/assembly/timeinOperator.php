<?php
require_once __DIR__ . '/../header.php';



$id = $input['id'] ?? null;
$itemID = $input['itemID'] ?? null;
$reference_no = $input['reference_no'] ?? null;
$material_no = $input['material_no'] ?? null;
$total_qty = $input['total_qty'] ?? null;
$full_name = $input['full_name'] ?? null;
$material_description = $input['material_description'] ?? null;
$time_in = date('Y-m-d H:i:s');
$model = $input['model'] ?? null; 
$shift = $input['shift'] ?? null; 
$lot_no = $input['lot_no'] ?? null; 
$date_needed = $input['date_needed'] ?? null; 




    try {
        // Begin transaction
        $db->beginTransaction();

        $sqlUpdate = "UPDATE delivery_form_new SET section = 'ASSEMBLY' WHERE id = :id";
        $paramsUpdate = [
            ':id' => $id,
        ];
        $updated = $db->Update($sqlUpdate, $paramsUpdate);

     // Step 1: Get the latest pending_quantity for the same reference_no
            $sqlGetLatestPending = "SELECT pending_quantity 
                                    FROM assembly_list_new 
                                    WHERE reference_no = :reference_no 
                                    ORDER BY time_in DESC 
                                    LIMIT 1";

            $paramsGetLatestPending = [
                ':reference_no' => $reference_no
            ];

            $latestRecord = $db->SelectOne($sqlGetLatestPending, $paramsGetLatestPending);

            // Step 2: Use the value if found, else fallback to $total_qty
       if ($latestRecord && isset($latestRecord['pending_quantity']) && $latestRecord['pending_quantity'] !== null) {
    $pending_quantity = (int)$latestRecord['pending_quantity'];
} else {
    $pending_quantity = (int)$total_qty;
}


            // Step 3: Proceed to insert
            $sqlInsert = "INSERT INTO assembly_list_new
                (itemID, model, shift, lot_no, date_needed, reference_no, material_no, material_description, pending_quantity, total_quantity, person_incharge, time_in, status, section, created_at)
                VALUES 
                (:itemID, :model, :shift, :lot_no, :date_needed, :reference_no, :material_no, :material_description, :pending_quantity, :total_qty, :person_incharge, :time_in, :status, :section, :time_in)";

            $paramsInsert = [
                ':itemID'               => $itemID,
                ':model'                => $model,
                ':shift'                => $shift,
                ':lot_no'               => $lot_no,
                ':date_needed'         => $date_needed,
                ':reference_no'         => $reference_no,
                ':material_no'          => $material_no,
                ':material_description' => $material_description,
                ':pending_quantity'     => $pending_quantity,  // <-- dynamic value here
                ':total_qty'            => $total_qty,
                ':person_incharge'      => $full_name,
                ':time_in'              => $time_in,
                ':status'               => 'pending',
                ':section'              => 'assembly'
            ];

            // Execute insert
            $insertedId = $db->Insert($sqlInsert, $paramsInsert);

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

            $deductQty = $total_qty * $usageType;
            $newInventory = max(0, $currentInventory - $deductQty);

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
        $db->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Update and insert successful. Inventory updated.',
        ]);
    } catch (PDOException $e) {
        $db->rollBack();
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'DB Error: ' . $e->getMessage()]);
    } catch (Exception $e) {
        $db->rollBack();
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
