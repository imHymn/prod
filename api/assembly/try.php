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

// Sample input
$material_no = '80035725';
$total_qty = 30; // Quantity to deduct

try {
    // Start transaction
    $db->beginTransaction();

    // Fetch all components for the given material_no
    $sqlComponents = "SELECT components_name, usage_type, actual_inventory 
                      FROM components_inventory 
                      WHERE material_no = :material_no";
    $components = $db->Select($sqlComponents, [':material_no' => $material_no]);

    if (!$components) {
        echo "No components found for material_no: $material_no\n";
        exit;
    }

    foreach ($components as $component) {
        $componentsName = $component['components_name'];
        $usageType = (int)$component['usage_type'];
        $currentInventory = (int)$component['actual_inventory'];

        // Calculate deduction
        $deductQty = $total_qty * $usageType;
        $newInventory = max(0, $currentInventory - $deductQty);

        // Debug output
        echo "Component: $componentsName\n";
        echo "Usage type: $usageType\n";
        echo "Before deduction: $currentInventory\n";
        echo "Deducting: $deductQty\n";
        echo "After deduction: $newInventory\n\n";

        // Update inventory
        $sqlUpdate = "UPDATE components_inventory 
                      SET actual_inventory = :new_inventory 
                      WHERE material_no = :material_no AND components_name = :components_name";
        $db->Update($sqlUpdate, [
            ':new_inventory' => $newInventory,
            ':material_no' => $material_no,
            ':components_name' => $componentsName,
        ]);
    }

    // Commit transaction
    $db->commit();
    echo "Inventory update complete.\n";

} catch (Exception $e) {
    $db->rollBack();
    echo "Error occurred: " . $e->getMessage() . "\n";
}
