<?php
require_once __DIR__ . '/../header.php';

use Model\RM_WarehouseModel;

try {
    $rmModel = new RM_WarehouseModel($db);
    $users = $rmModel->getComponents();
    echo json_encode($users);
} catch (PDOException $e) {
    echo "DB Error: " . $e->getMessage();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
