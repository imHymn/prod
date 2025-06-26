<?php
require_once __DIR__ . '/../header.php';

use Model\DeliveryModel;

try {
    if (!isset($_GET['model_name']) || !isset($_GET['customer_name'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing required parameters']);
        exit;
    }

    $modelName = trim($_GET['model_name']);
    $customerName = trim($_GET['customer_name']);

    $model = new DeliveryModel($db);
    $components = $model->getAllComponents($modelName, $customerName);

    echo json_encode($components);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'DB Error: ' . $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error: ' . $e->getMessage()]);
}
