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


    $id = $input['id'] ?? null;
    $material_no = $input['material_no'] ?? null;
    $material_description = $input['material_description'] ?? null;
    $name = $input['name'] ?? null;
    $quantity = $input['quantity'] ?? null;
    $inputQuantity = $input['inputQuantity'] ?? null;

    $timein = date('Y-m-d H:i:s');

    $sql = "UPDATE `stamping` SET person_incharge=:name,time_in=:timein,status=:status WHERE id=:id";
        $sqlParams = [
            ':name'=>$name,
            ':timein'=>$timein,
            ':id'=>$id,
            ':status'=>'ongoing'
        ];
    
    $result = $db->Update($sql, $sqlParams);


    if ($result) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Both records updated successfully'
        ]);
    } else {
        throw new Exception("One or both updates failed");
    }

} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => "DB Error: " . $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => "Error: " . $e->getMessage()]);
}
