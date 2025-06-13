<?php
require_once __DIR__ . '/../header.php';

use Model\WarehouseModel;

try {
    $warehouseModel = new WarehouseModel($db);
    $customers = $warehouseModel->getPullingHistory();
    echo json_encode($customers);
} catch (PDOException $e) {
    echo "DB Error: " . $e->getMessage();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
