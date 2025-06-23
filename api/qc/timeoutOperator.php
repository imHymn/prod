<?php
require_once __DIR__ . '/../header.php';

use Model\QCModel;
use Validation\QCValidator;

$data = [
    'id' => $input['id'] ?? null,
    'name' => $input['name'] ?? null,
    'time_out' => date('Y-m-d H:i:s'),
    'model' => $input['model'] ?? null,
    'quantity' => $input['quantity'] ?? null,
    'good' => $input['good'] ?? null,
    'no_good' => $input['nogood'] ?? null,
    'replace' => $input['replace'] ?? null,
    'rework' => $input['rework'] ?? null,
    'total_quantity' => $input['total_quantity'] ?? null,
    'reference_no' => $input['reference_no'] ?? null,
    'material_description' => $input['material_description'] ?? null,
    'material_no' => $input['material_no'] ?? null,
    'shift' => $input['shift'] ?? null,
    'lot_no' => $input['lot_no'] ?? null,
    'date_needed' => $input['date_needed'] ?? null,
    'pending_quantity' => $input['pending_quantity'] ?? null,
];

if ($data['pending_quantity'] > 0) {
    $data['pending_quantity'] -= $data['quantity'];
} elseif ($data['pending_quantity'] === null) {
    $data['pending_quantity'] = $data['total_quantity'] - $data['quantity'];
}

$errors = QCValidator::validateTimeOutQC($data);
if (!empty($errors)) {
    echo json_encode([
        'success' => false,
        'message' => $errors
    ]);
    exit;
}

try {
    $qcModel = new QCModel($db);
    $db->beginTransaction();

    $qcModel->updateQCListTimeout($data);

    $newStatus = '';
    $newSection = '';

    if ($data['reference_no']) {
        try {
            $result = $qcModel->getQCTotalSummary($data['reference_no']);

            if ($result) {
                $totalDone = (int)$result['total_done'];
                $totalQuantity = (int)$result['total_required'];
                $total_good = (int)$result['total_good'];
                $total_no_good = (int)$result['total_no_good'];
                $total_rework = (int)$result['total_rework'];
                $total_replace = (int)$result['total_replace'];

                if ($totalDone === $totalQuantity) {
                    if ($total_no_good > 0) {
                        // Add missing fields to $data so insertReworkAssembly works
                        $data['total_replace'] = $total_replace;
                        $data['total_rework'] = $total_rework;
                        $data['total_no_good'] = $total_no_good;

                        $qcModel->insertReworkAssembly($data);
                        $newStatus = 'pending';
                        $newSection = 'rework';
                    }


                    $warehouseData = [
                        'reference_no' => $data['reference_no'],
                        'material_no' => $data['material_no'],
                        'material_description' => $data['material_description'],
                        'model' => $data['model'],
                        'total_good' => $total_good,
                        'total_quantity' => $totalQuantity,
                        'shift' => $data['shift'],
                        'lot_no' => $data['lot_no'],
                        'date_needed' => $data['date_needed'],
                        'created_at' => $data['time_out'],
                        'new_section' => 'warehouse',
                        'new_status' => 'done',
                    ];
                    $qcModel->moveToFGWarehouse($warehouseData);

                    if ($data['no_good'] > 0) {
                        $newStatus = 'pending';
                        $newSection = 'rework';
                    } else {
                        $newStatus = 'pending';
                        $newSection = 'warehouse';
                    }
                } else {
                    $qcModel->duplicatePendingQCRow($data['id'], $data['pending_quantity'], $data['time_out']);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'No data found for that reference number.']);
                $db->rollBack();
                exit;
            }
        } catch (PDOException $e) {
            $db->rollBack();
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'DB Error: ' . $e->getMessage()]);
            exit;
        }

        $db->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Update and insert successful. Inventory updated.',
            'Pending Quantity' => $data['pending_quantity']
        ]);
    }
} catch (PDOException $e) {
    $db->rollBack();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'DB Error: ' . $e->getMessage()]);
} catch (Exception $e) {
    $db->rollBack();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
