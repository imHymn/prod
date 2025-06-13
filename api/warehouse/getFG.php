<?php
require_once __DIR__ . '/../header.php';

use Model\WarehouseModel;

try {
    $warehouseModel = new WarehouseModel($db);

    // Use the Select method to fetch data
    $customers = $warehouseModel->getFGWarehouse();
    // Return the results as a JSON response
    echo json_encode($customers);
} catch (PDOException $e) {
    echo "DB Error: " . $e->getMessage();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
