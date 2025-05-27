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

require_once __DIR__ . '/../../Classes/Database/DatabaseClass.php';
$db = new DatabaseClass();

// 🔄 Read JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Safely extract values
$id = $input['id'] ?? null;
$full_name = $input['full_name'] ?? null;
$person_incharge = $full_name;       // Assuming person_incharge is the full_name
$time_out = date('Y-m-d H:i:s');      // Current time
$lot_no = $input['lot_no'] ?? null;
$replace = $input['replace']??null;
$rework = $input['rework']??null;
    try {
        // Begin transaction
        $db->beginTransaction();
    $status_rework='done';
    $status_qc='pending';
        $section='QC';
        $sqlUpdate = "UPDATE assembly_list SET rework_timeout = :time_out,status_rework=:status_rework ,status_qc=:status_qc,prev_section=:prev_section WHERE id = :id";
        $paramsUpdate = [
            ':time_out' => $time_out,
            ':id' => $id,
                ':status_rework'=>$status_rework,
                'status_qc'=>$status_qc,
                ':prev_section'=>$section,
        ];
        $updated = $db->Update($sqlUpdate, $paramsUpdate);
       
        // Commit transaction
        $db->commit();
        $section = 'QC';
    
        $sqlInsertDelivery = "UPDATE delivery_forms SET section = :section,status=:status_rework WHERE id = :id AND lot_no = :lot_no";

        $paramsInsertDelivery = [
            ':section' => $section,
            ':status_rework'=>$status_rework,
            ':id' => $id,
            ':lot_no' => $lot_no,
        ];

        // Use Update() for an UPDATE query, not Insert()
        $updatedDelivery = $db->Update($sqlInsertDelivery, $paramsInsertDelivery);
// Fetch components for the material_no (you need material_no input too)
$material_no = $input['material_no'] ?? null;

if ($material_no && $rework !== null) {
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
        $returnQty = $rework * $usageType;
        $newInventory = $currentInventory + $returnQty;

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


        echo json_encode([
            'success' => true,
            'message' => 'Update and insert successful',
            'rows_updated' => $updated,
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

