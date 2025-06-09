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
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    $material_no = $input['material_no'] ?? null;
    $components_name = $input['components_name'] ?? null;
    $batch = $input['batch'] ?? null;

    if (!$material_no || !$components_name) {
        throw new Exception("Missing required parameters.");
    }

    // Fetch stages for the component
    $sql = "SELECT stage_name,section,stage, status 
            FROM stamping 
            WHERE material_no = :material_no 
              AND components_name = :components_name AND batch=:batch
            ORDER BY stage ASC";

    $params = [
        ':material_no' => $material_no,
        ':components_name' => $components_name,
        ':batch'=>$batch
    ];

    $stages = $db->Select($sql, $params);

    if (!$stages) {
        echo json_encode([
            'status' => 'error',
            'message' => 'No stages found for this component.'
        ]);
        exit;
    }

    echo json_encode([
        'status' => 'success',
        'stages' => $stages
    ]);
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>
