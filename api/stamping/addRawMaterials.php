<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../env');
$dotenv->load();

require_once __DIR__ . '/../../Classes/Database/DatabaseClass.php';
$db = new DatabaseClass();

try {
    $input = json_decode(file_get_contents('php://input'), true);

    if (
        !isset($input['material_no'], $input['components_name'], $input['raw_material_name'], $input['actual_inventory'], $input['usage_type'])
    ) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing required fields.']);
        exit;
    }

    $materialNo = $input['material_no'];
    $componentsName = $input['components_name'];
    $rawMaterialName = $input['raw_material_name'];
    $actualInventory = $input['actual_inventory'];
    $usageType = $input['usage_type'];

    $critical = 90 * $usageType;
    $minimum = 2 * $critical;
    $reorder = 3 * $critical;
    $maximum_inventory = 450 * $usageType;
    $normal = 4 * $critical;

    // First, check if the record already exists
    $checkSql = "SELECT * FROM rawmaterials_inventory 
                 WHERE material_no = :material_no 
                 AND material_description = :components_name 
                 AND raw_material = :raw_material_name";

    $checkParams = [
        ':material_no' => $materialNo,
        ':components_name' => $componentsName,
        ':raw_material_name' => $rawMaterialName
    ];

    $existing = $db->SelectOne($checkSql, $checkParams);

    if ($existing) {
        // Perform update
        $updateSql = "UPDATE rawmaterials_inventory 
                      SET actual_inventory = :actual_inventory,
                          `usage` = :usage_type,
                          critical = :critical,
                          minimum = :minimum,
                          reorder = :reorder,
                          normal = :normal,
                          maximum_inventory = :maximum_inventory
                      WHERE material_no = :material_no
                        AND material_description = :components_name
                        AND raw_material = :raw_material_name";

        $updateParams = array_merge($checkParams, [
            ':actual_inventory' => $actualInventory,
            ':usage_type' => $usageType,
            ':critical' => $critical,
            ':minimum' => $minimum,
            ':reorder' => $reorder,
            ':normal' => $normal,
            ':maximum_inventory' => $maximum_inventory
        ]);

        $result = $db->Update($updateSql, $updateParams);

        if ($result !== false) {
            echo json_encode(['success' => true, 'message' => 'Material updated successfully.']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Update failed.']);
        }

    } else {
        // Perform insert
        $insertSql = "INSERT INTO rawmaterials_inventory 
            (material_no, material_description, raw_material, actual_inventory, `usage`, critical, minimum, reorder, normal, maximum_inventory) 
            VALUES 
            (:material_no, :components_name, :raw_material_name, :actual_inventory, :usage_type, :critical, :minimum, :reorder, :normal, :maximum_inventory)";

        $insertParams = [
            ':material_no' => $materialNo,
            ':components_name' => $componentsName,
            ':raw_material_name' => $rawMaterialName,
            ':actual_inventory' => $actualInventory,
            ':usage_type' => $usageType,
            ':critical' => $critical,
            ':minimum' => $minimum,
            ':reorder' => $reorder,
            ':normal' => $normal,
            ':maximum_inventory' => $maximum_inventory
        ];

        $result = $db->Insert($insertSql, $insertParams);

        if ($result !== false) {
            echo json_encode(['success' => true, 'message' => 'Material added successfully.']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Insert failed.']);
        }
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'DB Error: ' . $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error: ' . $e->getMessage()]);
}
