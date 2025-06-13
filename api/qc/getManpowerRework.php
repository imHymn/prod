<?php
require_once __DIR__ . '/../header.php';

use Model\QCModel;

try {
    $qcModel = new QCModel($db);
    $customers = $qcModel->getManpowerReworkData();
    echo json_encode($customers);
} catch (PDOException $e) {
    echo "DB Error: " . $e->getMessage();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
