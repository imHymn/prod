<?php
require_once __DIR__ . '/../header.php';

use Model\CycleTimeModel;

try {
  $model = new CycleTimeModel($db);
  $data = $model->getQCCycleTimes(); // or getAssemblyCycleTimes()
  echo json_encode($data);
  exit;
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(["error" => $e->getMessage()]);
}
