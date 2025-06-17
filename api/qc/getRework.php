<?php
require_once __DIR__ . '/../header.php';

use Model\QCModel;

try {
    // echo "Creating QCModel\n";
    $qcModel = new QCModel($db);
    // echo "Calling getRework()\n";
    $customers = $qcModel->getRework();
    // echo "Got result\n";
    echo json_encode($customers);
} catch (PDOException $e) {
    echo json_encode(["error" => "DB Error: " . $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(["error" => "Error: " . $e->getMessage()]);
}
