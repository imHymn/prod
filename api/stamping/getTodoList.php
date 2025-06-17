<?php
require_once __DIR__ . '/../header.php';

use Model\StampingModel;
use Validation\StampingValidator;

try {
    $section = $_GET['section'] ?? null;

    $errors = StampingValidator::validateSection($section);
    if (!empty($errors)) {
        echo json_encode([
            'success' => false,
            'message' => $errors
        ]);
    }

    $stampingModel = new StampingModel($db);
    if ($section === "all") {
        $data = $stampingModel->getTodoListAllSection();
    } else {

        $data = $stampingModel->getTodoListSpecificSection($section);
    }

    echo json_encode($data);
} catch (PDOException $e) {
    echo json_encode(['error' => 'DB Error: ' . $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['error' => 'Error: ' . $e->getMessage()]);
}
