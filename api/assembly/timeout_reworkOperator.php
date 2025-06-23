<?php
require_once __DIR__ . '/../header.php';

use Model\AssemblyModel;

$id = $input['id'] ?? null;
$full_name = $input['full_name'] ?? null;
$time_out = date('Y-m-d H:i:s');
$inputQty = $input['inputQty'] ?? null;
$replace = $input['replace'] ?? null;
$rework = $input['rework'] ?? null;
$reference_no = $input['reference_no'] ?? null;
$quantity = $input['quantity'] ?? null;

$assembly_pending_quantity = $input['assembly_pending_quantity'] ?? null;
if ($assembly_pending_quantity === null) {
    $assembly_pending_quantity = $quantity - $inputQty;
} else {
    $assembly_pending_quantity = $assembly_pending_quantity - $inputQty;
}


if (!$id || !$full_name) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing required data.']);
    exit;
}

try {
    $db->beginTransaction();

    $assemblyModel = new AssemblyModel($db);

    $updated = $assemblyModel->updateReworkAssemblyTimeout([
        'id' => $id,
        'full_name' => $full_name,
        'time_out' => $time_out,
        'replace' => $replace,
        'rework' => $rework,
        'assembly_pending_quantity' => $assembly_pending_quantity
    ]);

    $insertedCount = 0;

    if ($reference_no) {
        $result = $assemblyModel->getGroupedAssemblyByReference($reference_no);

        if ($result) {
            $total_rework = (int)$result['total_rework'];
            $total_replace = (int)$result['total_replace'];
            $total = $total_rework + $total_replace;
            $total_quantity = (int)$result['total_quantity'];
            $material_no = $result['material_no'];
            if ($total === $total_quantity) {

                if ($material_no && $total_replace > 0) {
                    $assemblyModel->updateComponentInventoryAfterReplace($material_no,  $total_replace, $time_out, $reference_no,);
                }
                $assemblyModel->markReworkAssemblyAsDone($reference_no);
                $assemblyModel->insertReworkQC($reference_no, $result, $total, $time_out);
            } else {
                $insertedCount = $assemblyModel->duplicateReworkAssembly($id, $replace, $rework, $inputQty, $time_out);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'No data found for that reference number.']);
        }
    }

    $db->commit();

    echo json_encode([
        'success' => true,
        'message' => "Update and optional duplication completed successfully.",
        'insertedCount' => $insertedCount,
        'assembly' => $assembly_pending_quantity,
        'rework' => $rework,
        'replace' => $replace
    ]);
} catch (Exception $e) {
    $db->rollback();
    error_log("Error during duplication: " . $e->getMessage());

    echo json_encode([
        'success' => false,
        'message' => "Operation failed: " . $e->getMessage()
    ]);
}
