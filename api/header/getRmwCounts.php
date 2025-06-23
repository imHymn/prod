<?php
require_once __DIR__ . '/../header.php';

use Model\HeaderModel;

try {
    $model = new HeaderModel($db);
    $counts = $model->getRmwCounts();
    echo json_encode(['success' => true, 'data' => $counts]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
