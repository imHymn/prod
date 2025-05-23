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
$lot_no = $input['lot_no'] ?? null;
$shift = $input['shift'];
$total_qty = $input['total_qty'] ?? null;
$quantity = $input['quantity'] ?? null;
$supplement_order = $input['supplement_order'] ?? null;
$date_needed = $input['date_needed'] ?? null;
$person_incharge = $full_name;
$time_in = date('Y-m-d H:i:s');

if ($id !== null && $full_name !== null && $model !== null && $material_no !== null) {
    try {
        // Begin transaction
        $db->beginTransaction();

        // Step 1: Update delivery_forms to set handler_name
        $sqlUpdate = "UPDATE delivery_forms SET handler_name = :full_name WHERE id = :id";
        $paramsUpdate = [
            ':full_name' => $full_name,
            ':id' => $id
        ];
        $updated = $db->Update($sqlUpdate, $paramsUpdate);
        $status_assembly='pending';
        $section='ASSEMBLY';
        // Step 2: Insert or update record in assembly_list
        $sqlInsert = "INSERT INTO assembly_list 
            (id, model, material_no, lot_no, total_qty, person_incharge, time_in,shift,status_assembly,section, date_needed)
            VALUES 
            (:id, :model, :material_no, :lot_no, :total_qty, :person_incharge, :time_in,:shift,:status_assembly,:section, :date_needed)
           ";
                
        $paramsInsert = [
            ':id' => $id,
            ':model' => $model,
            ':material_no' => $material_no,
            ':lot_no' => $lot_no,
            ':total_qty' => $total_qty,
            ':person_incharge' => $person_incharge,
            ':time_in' => $time_in,
            ':section'=>$section,
            ':shift'=>$shift,
            ':status_assembly'=>$status_assembly,
            ':date_needed' => $date_needed,
        ];

        $insertedId = $db->Insert($sqlInsert, $paramsInsert);

        // Step 3: Update section to 'ASSEMBLY' in delivery_forms
        $sqlInsertDelivery = "UPDATE delivery_forms SET section = :section WHERE id = :id AND lot_no = :lot_no";
        $paramsInsertDelivery = [
            ':section' => 'ASSEMBLY',
            ':id' => $id,
            ':lot_no' => $lot_no,
        ];
        $updatedDelivery = $db->Update($sqlInsertDelivery, $paramsInsertDelivery);

        // === Inventory deduction block for ALL components of this material_no ===
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

            // Calculate deduction
            $deductQty = $total_qty * $usageType;
            $newInventory = max(0, $currentInventory - $deductQty);

            // Update inventory
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

        // === End inventory deduction block ===

        // Commit transaction
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
} else {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing required data (id, full_name, model, material_no)']);
}
