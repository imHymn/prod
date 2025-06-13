<?php
require_once __DIR__ . '/../header.php';

use Model\WarehouseModel;
use Validation\WarehouseValidator;

// Collect all necessary data
$data = [
    'id' => $input['id'] ?? null,
    'material_no' => $input['material_no'] ?? null,
    'material_description' => $input['material_description'] ?? null,
    'total_quantity' => $input['total_quantity'] ?? null,
    'reference_no' => $input['reference_no'] ?? null,
    'pulled_at' => date('Y-m-d H:i:s'),
];

// Validate input
$errors = WarehouseValidator::validatePulledOutWarehouse($data);
if (!empty($errors)) {
    echo json_encode([
        'success' => false,
        'message' => $errors
    ]);
    exit;
}

try {
    $db->beginTransaction();
    $warehouseModel = new WarehouseModel($db);

    $updatedWarehouse = $warehouseModel->markAsPulledFromFG($data['id'], $data['pulled_at']);
    $updatedDelivery  = $warehouseModel->markDeliveryFormAsDone($data['reference_no']);
    $updatedAssembly  = $warehouseModel->markAssemblyListAsDone($data['reference_no']);
    $updatedInventory = $warehouseModel->updateMaterialInventory($data['material_no'], $data['material_description'], $data['total_quantity']);

    $db->commit();

    if ($updatedWarehouse && $updatedAssembly && $updatedInventory) {
        echo json_encode(['success' => true, 'message' => 'Item marked as PULLED OUT']);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'No records were updated'
        ]);
    }
} catch (Exception $e) {
    $db->rollBack();
    echo json_encode([
        'success' => false,
        'message' => '❌ Error: ' . $e->getMessage()
    ]);
}
