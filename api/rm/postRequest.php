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

    // Extract variables from input
    $id = $input['id'] ?? null;
    $material_no = $input['material_no'] ?? null;
    $component_name = $input['component_name'] ?? null;
    $quantity = $input['quantity'] ?? null;
    $process_quantity = $input['process_quantity'] ?? null;
    $created_at = date('Y-m-d H:i:s');
    if (!$id || !$material_no || !$component_name) {
        throw new Exception("Missing required fields: id, material_no, or component_name");
    }

   try {
    // Start transaction
    $db->beginTransaction();

    // 1. Update pending_rmwarehouse
    $sql1 = "UPDATE `pending_rmwarehouse` 
             SET status = :status1, section = :section1 
             WHERE id = :id";

    $params1 = [
        ':status1' => 'done',
        ':section1' => 'stamping',
        ':id' => $id
    ];

    $result1 = $db->Update($sql1, $params1);

    // 2. Update components_inventory
    $sql2 = "UPDATE `components_inventory` 
             SET status = :status2, section = :section2, rm_stocks = :rm_stocks
             WHERE material_no = :material_no AND components_name = :components_name";

    $params2 = [
        ':status2' => 'done',
        ':section2' => 'stamping',
        ':rm_stocks' => $quantity,
        ':material_no' => $material_no,
        ':components_name' => $component_name
    ];

    $result2 = $db->Update($sql2, $params2);
$dateToday = date('Ymd');
$prefix = $dateToday . '-%';
$sqlCount = "SELECT COUNT(*) as count FROM stamping WHERE reference_no LIKE :prefix";
$countResult = $db->SelectOne($sqlCount, [':prefix' => $prefix]);
$existingCount = $countResult ? (int)$countResult['count'] : 0;

 $result3 = true;
    $sqlInsert = "INSERT INTO `stamping` 
        (`material_no`, `components_name`, `process_quantity`, `stage`, `total_quantity`,pending_quantity, `status`, `reference_no`,created_at)
        VALUES (:material_no, :components_name, :process_quantity, :stage, :total_quantity,:pending_quantity, :status, :reference_no,:created_at)";

    for ($i = 1; $i <= (int)$process_quantity; $i++) {
        $referenceNo = $dateToday . '-' . str_pad($existingCount + $i, 4, '0', STR_PAD_LEFT);

        $paramsInsert = [
            ':material_no' => $material_no,
            ':components_name' => $component_name,
            ':process_quantity' => $process_quantity, // always insert 1 per stage
            ':stage' => $i,
            ':pending_quantity' => $quantity,
            ':total_quantity' => $quantity,
            ':status' => 'pending',
            ':reference_no' => $referenceNo,
            ':created_at' => $created_at,
        ];

        $inserted = $db->Insert($sqlInsert, $paramsInsert);
        if (!$inserted) {
            $result3 = false;
            break;
        }
}

    if ($result1 && $result2 && $result3) {
        // Commit transaction
        $db->commit();

        echo json_encode([
            'status' => 'success',
            'message' => 'All records updated and inserted successfully'
        ]);
    } else {
        // Rollback transaction if any fail
        $db->rollBack();

        throw new Exception("One or more database operations failed");
    }

} catch (PDOException $e) {
    // Rollback if exception caught

        $db->rollBack();
    
    echo json_encode(['status' => 'error', 'message' => "DB Error: " . $e->getMessage()]);
} catch (Exception $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    echo json_encode(['status' => 'error', 'message' => "Error: " . $e->getMessage()]);
}

} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => "DB Error: " . $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => "Error: " . $e->getMessage()]);
}
