<?php

use Model\AssemblyModel;

require_once __DIR__ . '/../header.php';

try {
    $model = new AssemblyModel($db);
    $customers = $model->getAllAssemblyData();
    echo json_encode($customers);
} catch (PDOException $e) {
    echo "DB Error: " . $e->getMessage();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
