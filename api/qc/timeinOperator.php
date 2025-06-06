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

// 🔄 Read JSON input
$input = json_decode(file_get_contents('php://input'), true);
date_default_timezone_set('Asia/Manila');

// Safely extract values
$id = $input['id'] ?? null;
$name = $input['name'] ?? null;
$time_in = date('Y-m-d H:i:s');


    try {
        // Begin transaction
        $db->beginTransaction();

        // Step 1: Update delivery_forms to set person_incharge_assembly
        $sqlUpdate = "UPDATE qc_list 
              SET person_incharge = :name,
                  time_in = :time_in
              WHERE id = :id";

        $paramsUpdate = [
            ':id' => $id,
            ':name' => $name,
            ':time_in'=>$time_in,

        ];
        $updated = $db->Update($sqlUpdate, $paramsUpdate);
        

        $db->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Update and insert successful. Inventory updated.',
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

