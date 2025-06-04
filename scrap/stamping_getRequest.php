<?php
// Show errors (for debugging)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include Composer autoloader and environment
require_once __DIR__ . '/../../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../env');
$dotenv->load();

require_once __DIR__ . '/../../Classes/Database/DatabaseClass.php';
$db = new DatabaseClass();

try {
    // Get input JSON
    $input = json_decode(file_get_contents("php://input"), true);

    $material_no = $input['material_no'] ?? null;
    $component_name = $input['component_name'] ?? null;

    if (!$material_no || !$component_name) {
        throw new Exception("Missing material_no or component_name");
    }

    $sql = "SELECT quantity FROM `pending_rmwarehouse` 
        WHERE material_no = :material_no 
          AND material_description = :component_name 
          AND status = 'done' 
          AND section = 'stamping' 
        ORDER BY id DESC 
        LIMIT 1";


    $params = [
        ':material_no' => $material_no,
        ':component_name' => $component_name
    ];

    $result = $db->Select($sql, $params);

    echo json_encode($result);

} catch (PDOException $e) {
    echo json_encode(['error' => 'DB Error: ' . $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['error' => 'Error: ' . $e->getMessage()]);
}
