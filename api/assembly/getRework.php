<?php
require_once __DIR__ . '/../header.php';

use Model\AssemblyModel;


try {
    $model = new AssemblyModel($db);
    $customers = $model->getReworkData();
    echo json_encode($customers);
} catch (PDOException $e) {
    echo "DB Error: " . $e->getMessage();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
