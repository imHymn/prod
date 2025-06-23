<?php
require_once __DIR__ . '/../header.php';

use Model\AssemblyModel;
use Validation\AssemblyValidator;

$assemblyData = [
    'id'               => $input['id'] ?? null,
    'itemID'               => $input['itemID'] ?? null,
    'model'                => $input['model'] ?? null,
    'shift'                => $input['shift'] ?? null,
    'lot_no'               => $input['lot_no'] ?? null,
    'date_needed'          => $input['date_needed'] ?? null,
    'reference_no'         => $input['reference_no'] ?? null,
    'material_no'          => $input['material_no'] ?? null,
    'material_description' => $input['material_description'] ?? null,
    'pending_quantity'     => $input['pending_quantity'] ?? null,
    'total_qty'            => $input['total_qty'] ?? null,
    'person_incharge'      => $input['full_name'] ?? null,
    'time_in'              => date('Y-m-d H:i:s'),
    'status'               => 'pending',
    'section'              => 'assembly',
];
$errors = AssemblyValidator::validateAssemblyDataTimeIn($assemblyData);
if (!empty($errors)) {
    echo json_encode([
        'success' => false,
        'message' => $errors
    ]);
    exit;
}
try {
    $db->beginTransaction();
    $assemblyModel = new AssemblyModel($db);

    $assemblyModel->updateDeliveryFormSection($assemblyData['id']);
    $pending_quantity = $assemblyModel->getLatestPendingQuantity($assemblyData['reference_no']) ?: (int)$assemblyData['total_qty'];

    $assemblyData['pending_quantity'] = $pending_quantity;
    $assemblyModel->insertAssemblyRecord($assemblyData);
    $assemblyModel->deductComponentInventory($assemblyData['material_no'], $assemblyData['reference_no'], $assemblyData['total_qty'], $assemblyData['time_in']);

    $db->commit();
    echo json_encode([
        'success' => true,
        'message' => 'Update and insert successful. Inventory updated.',
    ]);
} catch (PDOException $e) {
    $db->rollBack();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'DB Error: ' . $e->getMessage()]);
} catch (Exception $e) {
    $db->rollBack();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
