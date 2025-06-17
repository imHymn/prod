<?php
require_once __DIR__ . '/../header.php';

use Model\QCModel;

$stampingModel = new QCModel($db);

$id = $input['id'] ?? null;
$full_name = $input['full_name'] ?? null;
$time_out = date('Y-m-d H:i:s');
$inputQty = $input['inputQty'] ?? null;
$no_good = $input['no_good'] ?? null;
$good = $input['good'] ?? null;
$reference_no = $input['reference_no'] ?? null;
$quantity = $input['quantity'] ?? null;
$qc_pending_quantity = $input['qc_pending_quantity'] ?? null;
$summary = null;
if ($qc_pending_quantity === null) {
    $qc_pending_quantity = $quantity - $inputQty;
} else {
    $qc_pending_quantity -= $inputQty;
}

if (!$id || !$full_name) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing required data.']);
    exit;
}

try {
    $db->beginTransaction();

    // 1. Update rework_qc with timeout
    $stampingModel->updateReworkQcTimeout([
        ':full_name' => $full_name,
        ':id' => $id,
        ':time_out' => $time_out,
        ':no_good' => $no_good,
        ':good' => $good,
        ':qc_pending_quantity' => $qc_pending_quantity
    ]);

    $insertedCount = 0;

    // 2. Check and process reference_no logic
    if ($reference_no) {
        $summary = $stampingModel->getQcSummaryByReference($reference_no);

        if (!$summary || !isset($summary['total_good'], $summary['total_noGood'], $summary['total_quantity'])) {
            throw new Exception("Invalid or incomplete summary data.");
        }

        $total_good = (int)$summary['total_good'];
        $total_noGood = (int)$summary['total_noGood'];
        $total = $total_good + $total_noGood;
        $total_quantity = (int)$summary['total_quantity'];

        if ($total === $total_quantity) {
            $stampingModel->markQcReferenceDone($reference_no);
            $stampingModel->updateDeliveryFormSection($reference_no);
            $stampingModel->updateFgWarehouseQuantity($reference_no, $total_good);
        } else {
            $row = $stampingModel->getReworkQcById($id);
            if ($row) {
                $insertedCount = $stampingModel->duplicateReworkQc($row, $time_out);
            }
        }
    }


    $db->commit();

    echo json_encode([
        'success' => true,
        'message' => "QC Timeout and optional duplication completed.",
        'summary' => $summary,
        'reference' => $reference_no
    ]);
} catch (Exception $e) {
    $db->rollback();
    error_log("QC Timeout error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
