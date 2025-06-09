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
    $stage_name = $input['stage_name'] ?? null;

    $created_at = date('Y-m-d H:i:s');

    if (!$id || !$material_no || !$component_name) {
        throw new Exception("Missing required fields: id, material_no, or component_name");
    }

    if (is_string($stage_name)) {
        $stage_name = json_decode($stage_name, true);
    }

    $flattenedStages = [];
    if (is_array($stage_name)) {
        foreach ($stage_name as $section => $stages) {
            foreach ($stages as $stage) {
                $flattenedStages[] = [
                    'stage_name' => $stage,
                    'section' => $section
                ];
            }
        }
    }


    if (count($flattenedStages) !== (int)$process_quantity) {
        throw new Exception("Mismatch between process_quantity and number of stages");
    }

   try {
    $db->beginTransaction();

    // 1. Update components_inventory
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

    // 2. Generate reference number prefix
    $dateToday = date('Ymd');
    $prefix = $dateToday . '-%';
    $sqlCount = "SELECT COUNT(*) as count FROM stamping WHERE reference_no LIKE :prefix";
    $countResult = $db->SelectOne($sqlCount, [':prefix' => $prefix]);
    $existingCount = $countResult ? (int)$countResult['count'] : 0;
    $rmReferenceNo = $dateToday . '-' . str_pad($existingCount + 1, 4, '0', STR_PAD_LEFT);

    // 3. Insert into rm_warehouse
    $insertRM = "INSERT INTO `rm_warehouse` (`material_no`, `component_name`, `process_quantity`, `quantity`, `status`, `created_at`, `reference_no`) 
                 VALUES (:material_no, :component_name, :process_quantity, :quantity, :status, :created_at, :reference_no)";
    $paramsRM = [
        ':material_no' => $material_no,
        ':component_name' => $component_name,
        ':process_quantity' => $process_quantity,
        ':quantity' => $quantity,
        ':created_at' => $created_at,
        ':status' => 'pending',
        ':reference_no' => $rmReferenceNo,
    ];
    $result2 = $db->Update($insertRM, $paramsRM);

    // 4. Get last batch number for this material/component
    $sqlBatch = "SELECT MAX(batch) as last_batch FROM stamping WHERE material_no = :material_no AND components_name = :components_name";
    $lastBatchResult = $db->SelectOne($sqlBatch, [
        ':material_no' => $material_no,
        ':components_name' => $component_name
    ]);
    $nextBatch = $lastBatchResult && $lastBatchResult['last_batch'] ? ((int)$lastBatchResult['last_batch'] + 1) : 1;

    // 5. Insert into stamping
    $sqlInsert = "INSERT INTO `stamping` 
                  (`material_no`, `components_name`, `process_quantity`, `stage`, `stage_name`, `section`, `total_quantity`, `pending_quantity`, `status`, `reference_no`, `created_at`, `batch`)
                  VALUES (:material_no, :components_name, :process_quantity, :stage, :stage_name, :section, :total_quantity, :pending_quantity, :status, :reference_no, :created_at, :batch)";

    $result3 = true;
    for ($i = 1; $i <= (int)$process_quantity; $i++) {
        $referenceNo = $dateToday . '-' . str_pad($existingCount + $i, 4, '0', STR_PAD_LEFT);
        $paramsInsert = [
            ':material_no' => $material_no,
            ':components_name' => $component_name,
            ':process_quantity' => $process_quantity,
            ':stage' => $i,
            ':stage_name' => $flattenedStages[$i - 1]['stage_name'],
            ':section' => $flattenedStages[$i - 1]['section'],
            ':pending_quantity' => $quantity,
            ':total_quantity' => $quantity,
            ':status' => 'pending',
            ':reference_no' => $referenceNo,
            ':created_at' => $created_at,
            ':batch' => $nextBatch
        ];
        $inserted = $db->Insert($sqlInsert, $paramsInsert);
        if (!$inserted) {
            $result3 = false;
            break;
        }
    }

    // 6. Commit or rollback
    if ($result2 && $result3) {
        $db->commit();
        echo json_encode([
            'status' => 'success',
            'message' => 'All records updated and inserted successfully',
            'batch' => $nextBatch
        ]);
    } else {
        $db->rollBack();
        throw new Exception("One or more database operations failed");
    }

} catch (Exception $e) {
    $db->rollBack();
    echo json_encode(['status' => 'error', 'message' => "Error: " . $e->getMessage()]);
}


} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => "DB Error: " . $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => "Error: " . $e->getMessage()]);
}
