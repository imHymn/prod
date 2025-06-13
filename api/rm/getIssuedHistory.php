<?php
require_once __DIR__ . '/../header.php';

use Model\RM_WarehouseModel;


try {
    $rmModel = new RM_WarehouseModel($db);


    $results = $rmModel->getIssuedHistory();

    echo json_encode([
        'status' => 'success',
        'data' => $results
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'DB Error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
