<?php
require_once __DIR__ . '/../header.php';

use Model\HeaderModel;

try {
    $model = new HeaderModel($db);

    // Remove usage of $_GET['since']
    $counts = $model->getAssemblyCounts();

    echo json_encode([
        'success' => true,
        'data' => $counts
    ]);
} catch (PDOException $e) {
    echo json_encode(['error' => 'DB Error: ' . $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['error' => 'Error: ' . $e->getMessage()]);
}
