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

$input = json_decode(file_get_contents('php://input'), true);
// Check required fields presence
if (
    !isset($input['material_no'], $input['components_name'], $input['actual_inventory'], $input['usage_type'])
) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required fields.']);
    exit;
}

try {
    $db->beginTransaction();  // START TRANSACTION

    $materialNo = $input['material_no'];
    $componentsName = $input['components_name'];
    $actualInventory = $input['actual_inventory'];
    $usageType = $input['usage_type'];

    // Check if material_no OR components_name already exists (get full row)
    $checkSql = "SELECT * FROM components_inventory WHERE material_no = :material_no OR components_name = :components_name LIMIT 1";
    $checkParams = [
        ':material_no' => $materialNo,
        ':components_name' => $componentsName
    ];
    $existingRow = $db->SelectOne($checkSql, $checkParams);

    if ($existingRow) {
        // Record exists — update actual_inventory by adding new input value
        $updatedInventory = $existingRow['actual_inventory'] + $actualInventory;

        // Calculate thresholds (use usage_type from input)
        $critical = 90 * $usageType;
        $minimum = 2 * $critical;
        $reorder = 3 * $critical;
        $maximum_inventory = 450 * $usageType;
        $normal = 4 * $critical;

        // Update query
        $updateSql = "UPDATE components_inventory SET
                        actual_inventory = :actual_inventory,
                        usage_type = :usage_type,
                        critical = :critical,
                        minimum = :minimum,
                        reorder = :reorder,
                        normal = :normal,
                        maximum_inventory = :maximum_inventory
                      WHERE id = :id";

        $updateParams = [
            ':actual_inventory' => $updatedInventory,
            ':usage_type' => $usageType,
            ':critical' => $critical,
            ':minimum' => $minimum,
            ':reorder' => $reorder,
            ':normal' => $normal,
            ':maximum_inventory' => $maximum_inventory,
            ':id' => $existingRow['id'],
        ];

        $result = $db->Update($updateSql, $updateParams);

        if ($result !== false) {
            $db->commit();
            echo json_encode(['success' => true, 'message' => 'Material updated successfully.', 'updated_inventory' => $updatedInventory]);
        } else {
            $db->rollBack();
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Update failed.']);
        }
    } else {
        // Not exists — insert new record

        // Calculate thresholds
        $critical = 90 * $usageType;
        $minimum = 2 * $critical;
        $reorder = 3 * $critical;
        $maximum_inventory = 450 * $usageType;
        $normal = 4 * $critical;

        $insertSql = "INSERT INTO components_inventory 
            (material_no, components_name, actual_inventory, usage_type, critical, minimum, reorder, normal, maximum_inventory) 
            VALUES 
            (:material_no, :components_name, :actual_inventory, :usage_type, :critical, :minimum, :reorder, :normal, :maximum_inventory)";

        $insertParams = [
            ':material_no' => $materialNo,
            ':components_name' => $componentsName,
            ':actual_inventory' => $actualInventory,
            ':usage_type' => $usageType,
            ':critical' => $critical,
            ':minimum' => $minimum,
            ':reorder' => $reorder,
            ':normal' => $normal,
            ':maximum_inventory' => $maximum_inventory,
        ];

        $result = $db->Insert($insertSql, $insertParams);

        if ($result !== false) {
            $db->commit();
            echo json_encode(['success' => true, 'message' => 'Material added successfully.']);
        } else {
            $db->rollBack();
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Insert failed.']);
        }
    }
} catch (PDOException $e) {
    if ($db->rollBack()) {}
    http_response_code(500);
    echo json_encode(['error' => 'DB Error: ' . $e->getMessage()]);
} catch (Exception $e) {
    if ($db->rollBack()) {}
    http_response_code(500);
    echo json_encode(['error' => 'Error: ' . $e->getMessage()]);
}
