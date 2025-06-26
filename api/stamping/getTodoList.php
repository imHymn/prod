<?php
require_once __DIR__ . '/../header.php';

use Model\StampingModel;
use Validation\StampingValidator;

try {

    $model = new StampingModel($db);


    $data = $model->getTodoListAllSection();

    echo json_encode($data);
} catch (PDOException $e) {
    echo json_encode(['error' => 'DB Error: ' . $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['error' => 'Error: ' . $e->getMessage()]);
}
