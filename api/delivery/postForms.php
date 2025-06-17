<?php
ob_clean(); // Clear any previous output
require_once __DIR__ . '/../header.php';

use Model\DeliveryModel;
use Validation\DeliveryValidator;

$currentDateTime = date('Y-m-d H:i:s');
$model_name = $input[0]['model_name'] ?? null;
$today = date('Ymd');

// 1. Validate input
$validate = DeliveryValidator::validatePostForms($input);
if (!empty($validate)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Validation Failed',
        'errors' => $validate
    ]);
    exit;
}

$model = new DeliveryModel($db);

// 2. Get next lot number
try {
    $lot_value = $model->getNextLotNumber($model_name);
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Error fetching lot number: ' . $e->getMessage()
    ]);
    exit;
}

// 3. Check for insufficient stock
try {
    $insufficientStockItems = $model->getMaterialStock($input);
    if (!empty($insufficientStockItems)) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Some items do not have enough stock in inventory.',
            'insufficient_items' => $insufficientStockItems
        ]);
        exit;
    }
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'An error occurred while checking inventory.',
        'error' => $e->getMessage()
    ]);
    exit;
}

// 4. Get reference number
try {
    $lastNumber = (int)substr($model->selectReferenceNo($today), -4);
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'An error occurred while selecting reference number.',
        'error' => $e->getMessage()
    ]);
    exit;
}

// (Optional) 5. Recheck inventory – do NOT echo it unless it's needed
try {
    $model->recheckInventory($input); // silent success/failure
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'An error occurred during final inventory check.',
        'error' => $e->getMessage()
    ]);
    exit;
}

// 6. Process and return one final response
try {
    $db->beginTransaction();
    $response = $model->processDeliveryForm($input, $lot_value, $today, $currentDateTime);
    $db->commit();
    echo json_encode($response);

    exit;
} catch (Exception $e) {
    $db->rollback();
    echo json_encode([
        'status' => 'error',
        'message' => 'An error occurred while processing the delivery form.',
        'error' => $e->getMessage()
    ]);
    exit;
}
