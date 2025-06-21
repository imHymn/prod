<?php
require_once __DIR__ . '/../header.php';

use Model\CycleTimeModel;

$model = new CycleTimeModel($db);

try {
    $data = $model->getAssemblyProcessTimes(); // or getAssemblyCycleTimes()
    echo json_encode($data);
    exit;
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}
