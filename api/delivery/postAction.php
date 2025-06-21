<?php
require_once __DIR__ . '/../header.php';

use Model\DeliveryModel;

$input = json_decode(file_get_contents("php://input"), true);

$id = (int)($input['id'] ?? 0);
$truck = $input['truck'] ?? '';
$material_no = $input['material_no'] ?? '';
$model_name = $input['model_name'] ?? '';
$material_description = $input['material_description'] ?? '';
$total_quantity = (int)($input['total_quantity'] ?? 0);

try {
    $model = new DeliveryModel($db);
    $result = $model->postAction($id, $truck, $material_no, $model_name, $material_description, $total_quantity);
    echo json_encode(['status' => $result ? 'success' : 'fail']);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
