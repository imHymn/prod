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
    $material_description = $input['material_description'] ?? null;
    $name = $input['name'] ?? null;
    $quantity = $input['quantity'] ?? null;
     $inputQuantity = $input['inputQuantity'] ?? null;
  
    $timeout = date('Y-m-d H:i:s');

    $sql = "UPDATE `stamping` SET person_incharge=:name,time_out=:timeout,status=:status,quantity=:inputQuantity,updated_at=:updated_at WHERE id=:id";
        $sqlParams = [
            ':name'=>$name,
            ':timeout'=>$timeout,
            ':id'=>$id,
            ':inputQuantity'=>$inputQuantity,
            ':status'=>'done',
            ':updated_at'=>$timeout
        ];
    
            $result = $db->Update($sql, $sqlParams);
            $selectSql = "SELECT * FROM stamping WHERE id = :id";
            $selectParams = [':id' => $id];

            // Get the current row to extract reference_no
            $currentRow = $db->SelectOne($selectSql, $selectParams);

            if (!$currentRow) {
                throw new Exception("No stamping record found for ID: $id");
            }

            $referenceNo = $currentRow['reference_no'];

            // Query to get the sum of quantity and max of total_quantity for this reference_no
            $quantityCheckSql = "
                SELECT 
                    SUM(quantity) as total_quantity_done,
                    MAX(total_quantity) as max_total_quantity
                FROM stamping
                WHERE reference_no = :reference_no
            ";
            $quantityCheckParams = [':reference_no' => $referenceNo];
            $quantityStats = $db->SelectOne($quantityCheckSql, $quantityCheckParams);

            $totalDone = (int) ($quantityStats['total_quantity_done'] ?? 0);
            $maxTotal = (int) ($quantityStats['max_total_quantity'] ?? 0);

            if ($totalDone < $maxTotal) {
                // Define callback and insert SQL
                $modifyCallback = function($row) use ($inputQuantity) {
                    return [
                        'reference_no' => $row['reference_no'],
                        'material_no' => $row['material_no'],
                        'components_name' => $row['components_name'],
                        'process_quantity' => $row['process_quantity'],
                        'stage' => $row['stage'],
                        'total_quantity' => $row['total_quantity'],
                        'time_in' => null,
                        'time_out' => null,
                        'status' => 'pending',
                        'person_incharge' => null,
                        'created_at' => $row['created_at'],
                        'updated_at' => null,
                    ];
                };

                $insertSql = "INSERT INTO stamping (
                    reference_no, material_no, components_name, process_quantity,
                    stage, total_quantity, time_in, time_out, status,
                    person_incharge, created_at, updated_at
                ) VALUES (
                    :reference_no, :material_no, :components_name, :process_quantity,
                    :stage, :total_quantity, :time_in, :time_out, :status,
                    :person_incharge, :created_at, :updated_at
                )";

                $insertedCount = $db->DuplicateAndModify($selectSql, $selectParams, $modifyCallback, $insertSql);
            }
            // Get the current row to extract reference_no, material_no, etc.
            $referenceNo = $currentRow['reference_no'];
            $materialNo = $currentRow['material_no'];
            $componentsName = $currentRow['components_name'];
            $totalQuantity = (int) $currentRow['total_quantity'];
            $processQuantity = (int) $currentRow['process_quantity'];

            // Check if all stages (1 to process_quantity) have total quantity matching total_quantity
            // Check if all stages (1 to process_quantity) have total quantity matching total_quantity
$allStagesDone = true;

for ($stage = 1; $stage <= $processQuantity; $stage++) {
    $stageQuantitySql = "
        SELECT SUM(quantity) as total_stage_quantity
        FROM stamping
        WHERE material_no = :material_no 
        AND components_name = :components_name 
        AND stage = :stage
    ";
    $stageQuantityParams = [
        ':material_no' => $materialNo,
        ':components_name' => $componentsName,
        ':stage' => $stage
    ];
    $stageResult = $db->SelectOne($stageQuantitySql, $stageQuantityParams);
    $stageQuantity = (int) ($stageResult['total_stage_quantity'] ?? 0);

    if ($stageQuantity < $totalQuantity) {
        $allStagesDone = false;
        break;
    }
}


            // If all stages are completed with correct quantity, insert into components_inventory
            if ($allStagesDone) {
                $insertInventorySql = "
                    UPDATE components_inventory 
                    SET actual_inventory = actual_inventory + :quantity ,rm_stocks=:rm_stocks
                    WHERE material_no = :material_no AND components_name = :components_name
                ";

                $insertInventoryParams = [
                    ':material_no' => $materialNo,
                    ':components_name' => $componentsName,
                    ':quantity' => $totalQuantity,
                    ':rm_stocks'=>0
                ];

                $db->Update($insertInventorySql, $insertInventoryParams);
            }


    if ($result) {
    echo json_encode([
    'status' => 'success',
    'message' => 'Record updated successfully',
    'inputQuantity' => $inputQuantity // include it in the response if needed
]);

    } else {
        throw new Exception("One or both updates failed");
    }

} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => "DB Error: " . $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => "Error: " . $e->getMessage()]);
}
