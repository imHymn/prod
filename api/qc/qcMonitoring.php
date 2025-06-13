<?php
require_once __DIR__ . '/../header.php';

use Model\QCModel;

try {
    // SQL query to fetch customer names
    $qcModel = new QCModel($db);
    // Use the Select method to fetch data
    $users = $qcModel->qcMonitoring();
    // Return the results as a JSON response
    echo json_encode($users);
} catch (PDOException $e) {
    echo "DB Error: " . $e->getMessage();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
