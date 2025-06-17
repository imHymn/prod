<?php
require_once __DIR__ . '/../header.php';

use Model\StampingModel;

try {
    $model = new StampingModel($db);

    $model->updateStampingTimeout($input);

    $row = $model->getStampingById($input['id']);
    if (!$row) {
        throw new Exception("No stamping record found for ID: {$input['id']}");
    }

    $stats = $model->getQuantityStats($row['reference_no']);
    $totalDone = (int)($stats['total_quantity_done'] ?? 0);
    $maxTotal = (int)($stats['max_total_quantity'] ?? 0);

    if ($totalDone < $maxTotal) {
        $model->duplicateIfNotDone($row, $input['inputQuantity']);
    } else {
        $allDone = $model->areAllStagesDone(
            $row['material_no'],
            $row['components_name'],
            (int)$row['process_quantity'],
            (int)$row['total_quantity'],
            (int)$row['batch']
        );

        if ($allDone) {
            $model->updateInventoryAndWarehouse($row['material_no'], $row['components_name'], $row['total_quantity']);
        }
    }

    echo json_encode([
        'status' => 'success',
        'message' => 'Stamping timeout processed',
        'totalDone' => $totalDone,
        'maxTotal' => $maxTotal,
        'totalQuantity' => $row['total_quantity']
    ]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
