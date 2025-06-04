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

try {
    // Get raw POST data (JSON)
    $inputJSON = file_get_contents('php://input');
    $input = json_decode($inputJSON, true);

    if (!$input) {
        throw new Exception("Invalid JSON input");
    }

    // Extract variables from input
    $id = $input['id'] ?? null;
    $material_no = $input['material_no'] ?? null;
    $component_name = $input['component_name'] ?? null;
    $quantity = isset($input['quantity']) ? (int)$input['quantity'] : null;

    if (!$material_no || !$component_name || $quantity === null) {
        throw new Exception("Missing required fields: material_no, component_name, or quantity");
    }

    // Step 1: Verify section and status
    $sqlVerify = "SELECT actual_inventory, section, status FROM components_inventory 
                  WHERE material_no = :material_no AND components_name = :components_name";
    $paramsVerify = [
        ':material_no' => $material_no,
        ':components_name' => $component_name
    ];
    $resultVerify = $db->Select($sqlVerify, $paramsVerify);

    if (empty($resultVerify)) {
        throw new Exception("Component not found");
    }

    $component = $resultVerify[0];

    if (strtolower($component['section']) !== 'stamping' || strtolower($component['status']) !== 'done') {
        throw new Exception("Component is not eligible for stock addition (must be in stamping and done)");
    }

    $current_inventory = (int)$component['actual_inventory'];
    $new_inventory = $current_inventory + $quantity;

    // Step 2: Update actual_inventory
    $sqlUpdate = "UPDATE components_inventory 
                  SET actual_inventory = :actual_inventory, status = :status, section = :section 
                  WHERE material_no = :material_no AND components_name = :components_name";
    $paramsUpdate = [
        ':actual_inventory' => $new_inventory,
        ':status' => 'standby',
        ':section' => 'stamping',
        ':material_no' => $material_no,
        ':components_name' => $component_name
    ];

    $resultUpdate = $db->Update($sqlUpdate, $paramsUpdate);

    echo json_encode([
        'status' => 'success',
        'message' => 'Stock added successfully',
        'new_inventory' => $new_inventory
    ]);

} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => "DB Error: " . $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => "Error: " . $e->getMessage()]);
}
