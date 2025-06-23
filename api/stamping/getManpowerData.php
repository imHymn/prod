<?php
require_once __DIR__ . '/../header.php';

use Model\StampingModel;

try {
    $stampingModel = new StampingModel($db);
    $users = $stampingModel->getManpowerData();
    echo json_encode($users);
} catch (PDOException $e) {
    echo "DB Error: " . $e->getMessage();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
