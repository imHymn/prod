<?php
require_once __DIR__ . '/../header.php';

use Model\CycleTimeModel;

try {
    $cycleModel = new CycleTimeModel($db);
    $cycleTimes = $cycleModel->getStampingCycleTimes();

    echo json_encode([$cycleTimes]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "DB Error: " . $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => "Error: " . $e->getMessage()]);
}
