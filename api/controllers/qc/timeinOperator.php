<?php
session_start();
require_once __DIR__ . '/../../../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../../');
$dotenv->load();

require_once __DIR__ . '/../../../Classes/Database/DatabaseClass.php';
$db = new DatabaseClass();
date_default_timezone_set('Asia/Manila');

header('Content-Type: application/json');
$input = json_decode(file_get_contents('php://input'), true);

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

