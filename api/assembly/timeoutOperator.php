<?php
require_once __DIR__ . '/../header.php';

use Model\AssemblyModel;
use Validation\AssemblyValidator;

$id = $input['id'] ?? null;
$itemID = $input['itemID'] ?? null;
$full_name = $input['full_name'] ?? null;
$reference_no = $input['reference_no'] ?? null;
$material_no = $input['material_no'] ?? null;
$material_description = $input['material_description'] ?? null;
$done_quantity = $input['done_quantity'] ?? null;

$total_qty = $input['total_qty'] ?? null;

$model = $input['model'] ?? null;
$shift = $input['shift'] ?? null;
$lot_no = $input['lot_no'] ?? null;
$date_needed = $input['date_needed'] ?? null;
$inputQty = $input['inputQty'] ?? null;
$totalDone = null;
$result = null;

$pending_quantity = $input['pending_quantity'] ?? null;
$pending_quantity = $pending_quantity - $inputQty;
$time_out = date('Y-m-d H:i:s');
if ($pending_quantity > 0) {
    $status_assembly = 'pending';
    $section_assembly = 'assembly';
} else {
    $status_assembly = 'done';
    $section_assembly = 'qc';
}

$assemblyData = [
    'id'                   => $id,
    'itemID'               => $itemID,
    'full_name'            => $full_name,
    'reference_no'         => $reference_no,
    'material_no'          => $material_no,
    'material_description' => $material_description,
    'done_quantity'        => $done_quantity,
    'total_qty'            => $total_qty,
    'model'                => $model,
    'shift'                => $shift,
    'lot_no'               => $lot_no,
    'date_needed'          => $date_needed,
    'inputQty'             => $inputQty,
    'pending_quantity'     => $pending_quantity,
    'time_out'             => $time_out,
];

$errors = AssemblyValidator::validateAssemblyTimeOut($assemblyData);
if (!empty($errors)) {
    echo json_encode([
        'success' => false,
        'message' => $errors
    ]);
    exit;
}
try {
    // Begin transaction
    $db->beginTransaction();

    $assemblyModel = new AssemblyModel($db);
    $assemblyModel->updateAssemblyListTimeout($done_quantity, $pending_quantity, $itemID, $time_out, $status_assembly, $section_assembly);

    $currentPending = $assemblyModel->getPendingAssembly($id);


    $basePending = isset($currentPending['assembly_pending']) && $currentPending['assembly_pending'] !== null
        ? (int)$currentPending['assembly_pending']
        : (int)$currentPending['total_quantity'];

    $remainingPending = $basePending - (int)$inputQty;
    $remainingPending = max(0, $remainingPending);

    $assemblyModel->UpdateDeliveryFormPending($id, $remainingPending);

    if ($reference_no) {
        try {

            $result = $assemblyModel->getTotalDoneAndRequired($reference_no);

            if ($result) {
                $totalDone = (int)$result['total_done'];
                $totalQuantity = (int)$result['total_required'];

                if ($totalDone === $totalQuantity) {

                    $qcPayload = [
                        'model' => $model,
                        'shift' => $shift,
                        'lot_no' => $lot_no,
                        'date_needed' => $date_needed,
                        'reference_no' => $reference_no,
                        'material_no' => $material_no,
                        'material_description' => $material_description,
                        'total_quantity' => $total_qty,
                        'created_at' => $time_out
                    ];

                    $assemblyModel->moveToQCList($qcPayload);
                } else {

                    $assemblyModel->duplicateDeliveryFormWithPendingUpdate($id, $time_out, $remainingPending);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'No data found for that reference number.']);
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'DB Error: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'reference_no is required.']);
    }

    echo json_encode([
        'success' => true,
        'reference_no' => $reference_no,
        'done_quantity' => $totalDone,
        'message' => 'Update and insert successful',
        'result' => $result,
        'id' => $id,
        $pending_quantity
    ]);

    $db->commit();
} catch (PDOException $e) {
    $db->rollBack();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'DB Error: ' . $e->getMessage()]);
} catch (Exception $e) {
    $db->rollBack();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
