<?php
require_once __DIR__ . '/../header.php';

use Model\StampingModel;

try {
    $stampingModel = new StampingModel($db);
    $result = $stampingModel->startProcessingStage($input);

    if ($result) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Stage updated successfully'
        ]);
    } else {
        throw new Exception("Stage update failed");
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => "DB Error: " . $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => "Error: " . $e->getMessage()]);
}
