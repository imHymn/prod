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

// Read POST body and decode JSON
$input = json_decode(file_get_contents("php://input"), true);

$id = $input['id'];
$name = $input['name'];
$timein = date('Y-m-d H:i:s');

try {
    // Update delivery_forms

    // Update assembly_list
    $sqlAssembly = "UPDATE assembly_list 
        SET curr_section = 'qc', status_qc = 'pending', rework_incharge_qc = :name ,rework_timein_qc=:rework_timein_qc
        WHERE id = :id";
    $paramsAssembly = [
        ':id' => $id,
        ':name' => $name,
        ':rework_timein_qc'=>$timein
    ];
    $db->Update($sqlAssembly, $paramsAssembly);

    echo json_encode(['success' => true, 'message' => 'Records updated successfully']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database update failed', 'error' => $e->getMessage()]);
}
