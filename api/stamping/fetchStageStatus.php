<?php
require_once __DIR__ . '/../header.php';

use Model\StampingModel;

try {
    $stampingModel = new StampingModel($db);

    $data = [
        'material_no' => $input['material_no'] ?? null,
        'components_name' => $input['components_name'] ?? null,
        'batch' => $input['batch'] ?? null
    ];

    $stages = $stampingModel->fetchStageStatus($data);

    if (!$stages) {
        echo json_encode([
            'status' => 'error',
            'message' => 'No stages found for this component.'
        ]);
        exit;
    }

    echo json_encode([
        'status' => 'success',
        'stages' => $stages
    ]);
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
