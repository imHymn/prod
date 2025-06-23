<?php
require_once __DIR__ . '/../header.php';

use Validation\RM_WarehouseValidator;
use Model\RM_WarehouseModel;

try {

    $errors = RM_WarehouseValidator::validateStageProcessing($input);
    if (!empty($errors)) {
        echo json_encode(['success' => false, 'message' => $errors]);
        exit;
    }
    $id = $input['id'] ?? null;
    $material_no = $input['material_no'] ?? null;
    $component_name = $input['component_name'] ?? null;
    $quantity = $input['quantity'] ?? null;
    $process_quantity = $input['process_quantity'] ?? null;
    $stage_name = $input['stage_name'] ?? null;

    $created_at = date('Y-m-d H:i:s');

    try {

        $db->beginTransaction();
        $rmModel = new RM_WarehouseModel($db);

        $rmModel->updateComponentInventoryStatus($material_no, $component_name, $quantity);


        $dateToday = date('Ymd');
        $prefix = $dateToday . '-%';
        $sqlCount = "SELECT COUNT(*) as count FROM stamping WHERE reference_no LIKE :prefix";
        $countResult = $db->SelectOne($sqlCount, [':prefix' => $prefix]);
        $existingCount = $countResult ? (int)$countResult['count'] : 0;
        $rmReferenceNo = $dateToday . '-' . str_pad($existingCount + 1, 4, '0', STR_PAD_LEFT);


        $result2 = $rmModel->insertIntoRMWarehouse([
            'material_no' => $material_no,
            'component_name' => $component_name,
            'process_quantity' => $process_quantity,
            'quantity' => $quantity,
            'created_at' => $created_at,
            'reference_no' => $rmReferenceNo
        ]);

        $nextBatch = $rmModel->getNextStampingBatch($material_no, $component_name);

        $decodedStageGroup = json_decode($stage_name, true); // Make sure this is a JSON array of objects

        if (!is_array($decodedStageGroup)) {
            return 'Invalid stage_name JSON structure';
        }

        $flattenedStages = RM_WarehouseValidator::flattenStages($decodedStageGroup);


        $result3 = $rmModel->insertStampingStages([
            'material_no' => $material_no,
            'component_name' => $component_name,
            'process_quantity' => $process_quantity,
            'quantity' => $quantity,
            'created_at' => $created_at
        ], $flattenedStages, $existingCount, $dateToday, $nextBatch);
        if ($result3 !== true) {
            throw new Exception($result3);
        }
        if ($result2 && $result3) {
            $rmModel->updateIssuedRawmaterials($id, $material_no, $component_name, $quantity);
            $db->commit();
            echo json_encode([
                'status' => 'success',
                'message' => 'All records updated and inserted successfully',
                'batch' => $nextBatch
            ]);
        } else {
            $db->rollBack();

            $errorDetail = [
                'status' => 'error',
                'message' => 'One or more database operations failed',
                'result2' => $result2,
                'result3' => $result3,
                'errorInfo2' => $stmt2?->errorInfo(),
                'errorInfo3' => $stmt3?->errorInfo(),
            ];
            echo json_encode($errorDetail);
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
