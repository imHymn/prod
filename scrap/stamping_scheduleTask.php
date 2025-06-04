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
    $material_no = $input['material_no'] ?? null;
    $component_name = $input['component_name'] ?? null;
    $quantity = isset($input['quantity']) ? (int)$input['quantity'] : null;

    if (!$material_no || !$component_name || $quantity === null) {
        throw new Exception("Missing required fields: material_no, component_name, or quantity");
    }

    // Step 1: Insert into components_task table
    $sqlInsert = "INSERT INTO components_task (material_no, material_description, quantity, status)
                  VALUES (:material_no, :component_name, :quantity, :status)";
    $paramsInsert = [
        ':material_no' => $material_no,
        ':component_name' => $component_name,
        ':quantity' => $quantity,
        ':status' => 'pending'
    ];

    $resultInsert = $db->Insert($sqlInsert, $paramsInsert);

    // Step 2: Update components_inventory to deduct rm_stocks
    $sqlUpdate = "UPDATE components_inventory 
                  SET rm_stocks = rm_stocks - :quantity 
                  WHERE material_no = :material_no AND components_name = :component_name";
    $paramsUpdate = [
        ':quantity' => $quantity,
        ':material_no' => $material_no,
        ':component_name' => $component_name
    ];

    $resultUpdate = $db->Update($sqlUpdate, $paramsUpdate);

    echo json_encode([
        'status' => 'success',
        'message' => 'Task added and RM stock deducted successfully'
    ]);

} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => "DB Error: " . $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => "Error: " . $e->getMessage()]);
}
