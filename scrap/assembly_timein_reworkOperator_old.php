<?php
// Show errors (for debugging)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include Composer autoloader
require_once __DIR__ . '/../../vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../env');
$dotenv->load();

// Include database class
require_once __DIR__ . '/../../Classes/Database/DatabaseClass.php';
$db = new DatabaseClass();

// 🔄 Read JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Safely extract values
$id = $input['id'] ?? null;
$full_name = $input['full_name'] ?? null;
$material_no = $input['material_no'] ?? null;
$material_description = $input['material_description'] ?? null;
$model = $input['model'] ?? null;
$good = $input['good'] ?? null;
$not_good = $input['not_good'] ?? null;
$lot_no = $input['lot_no'] ?? null;
$shift = $input['shift'];
$total_qty = $input['total_qty'] ?? null;
$quantity = $input['quantity'] ?? null;
$supplement_order = $input['supplement_order'] ?? null;
$date_needed = $input['date_needed'] ?? null;
$person_incharge = $full_name;
$time_in = date('Y-m-d H:i:s');

    try {
        // Begin transaction
        $db->beginTransaction();

        // Step 1: Update delivery_forms to set person_incharge_assembly
        $sqlUpdate = "UPDATE delivery_forms SET person_incharge_rework = :full_name WHERE id = :id";
        $paramsUpdate = [
            ':full_name' => $full_name,
            ':id' => $id
        ];
        $updated = $db->Update($sqlUpdate, $paramsUpdate);
        $status_assembly='pending';
        $section='ASSEMBLY';
        // Step 2: Insert or update record in assembly_list
     $sqlInsert = "UPDATE assembly_list SET rework_incharge_assembly = :person_incharge, rework_timein = :time_in WHERE id = :id";
  
        $paramsInsert = [
            ':id' => $id,
            ':person_incharge' => $person_incharge,
            ':time_in' => $time_in,
        ];

        $insertedId = $db->Insert($sqlInsert, $paramsInsert);

        // Step 3: Update section to 'ASSEMBLY' in delivery_forms
        $sqlInsertDelivery = "UPDATE delivery_forms SET section = :section,status='pending' WHERE id = :id AND lot_no = :lot_no";
        $paramsInsertDelivery = [
            ':section' => 'ASSEMBLY',
            ':id' => $id,
            ':lot_no' => $lot_no,
        ];
        $updatedDelivery = $db->Update($sqlInsertDelivery, $paramsInsertDelivery);

        // === Inventory deduction block for ALL components of this material_no ===
    

        $db->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Update and insert successful. Inventory updated.',
            'rows_updated' => $updated,
            'inserted_id' => $insertedId,
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

