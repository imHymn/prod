<?php
require_once __DIR__ . '/../header.php';

use Model\DeliveryModel;

try {
    $model = new DeliveryModel($db);
    $customers = $model->getPulledOut();

    echo json_encode($customers);
} catch (PDOException $e) {
    echo "DB Error: " . $e->getMessage();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
