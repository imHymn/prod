<?php
require_once __DIR__ . '/../header.php';

use Model\AssemblyModel;

try {
    $assemblyModel = new AssemblyModel($db);
    $customers = $assemblyModel->getTodoList();
    echo json_encode($customers);
} catch (PDOException $e) {
    echo "DB Error: " . $e->getMessage();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
