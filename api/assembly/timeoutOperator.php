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

// 🔄 Read JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Safely extract values
$id = $input['id'] ?? null;
$full_name = $input['full_name'] ?? null;
$person_incharge = $full_name;       // Assuming person_incharge is the full_name
$time_out = date('Y-m-d H:i:s');      // Current time
$lot_no = $input['lot_no'] ?? null;

    try {
        // Begin transaction
        $db->beginTransaction();
    $status_assembly='done';
    $status_qc='pending';
        $section='QC';
        $sqlUpdate = "UPDATE assembly_list SET time_out = :time_out,status_assembly=:status_assembly ,status_qc=:status_qc,section=:section WHERE id = :id";
        $paramsUpdate = [
            ':time_out' => $time_out,
            ':id' => $id,
                ':status_assembly'=>$status_assembly,
                'status_qc'=>$status_qc,
                ':section'=>$section,
        ];
        $updated = $db->Update($sqlUpdate, $paramsUpdate);
       
        // Commit transaction
        $db->commit();
        $section = 'QC';
    
        $sqlInsertDelivery = "UPDATE delivery_forms SET section = :section,status=:status_assembly WHERE id = :id AND lot_no = :lot_no";

        $paramsInsertDelivery = [
            ':section' => $section,
            ':status_assembly'=>$status_assembly,
            ':id' => $id,
            ':lot_no' => $lot_no,
        ];

        // Use Update() for an UPDATE query, not Insert()
        $updatedDelivery = $db->Update($sqlInsertDelivery, $paramsInsertDelivery);


        echo json_encode([
            'success' => true,
            'message' => 'Update and insert successful',
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

