<?php
require_once __DIR__ . '/../header.php';

use Model\DeliveryModel;

try {
    $model = new DeliveryModel($db);
    $sql = $model->getAllCustomers();

    echo json_encode($sql);
} catch (PDOException $e) {
    echo "DB Error: " . $e->getMessage();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
