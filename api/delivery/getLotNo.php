<?php
require_once __DIR__ . '/../header.php';

use Model\DeliveryModel;
use Validation\DeliveryValidator;

try {
    $model_name = $_GET['model_name'];

    $errors = DeliveryValidator::validateModelName($model_name);
    if (!empty($errors)) {
        echo json_encode(['success' => false, 'errors' => $errors]);
        exit;
    }

    $model = new DeliveryModel($db);
    $sql = $model->getLatestLotNoByModel($model_name);

    echo json_encode($sql);
} catch (PDOException $e) {
    echo "DB Error: " . $e->getMessage();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
