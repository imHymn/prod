<?php
require_once __DIR__ . '/../header.php';



$id = $input['id'] ?? null;
$material_no = $input['material_no'] ?? null;
$material_description = $input['material_description'] ?? null;
$total_quantity = $input['total_quantity'] ?? null;
$reference_no = $input['reference_no'] ?? null;
$pulled_at = date('Y-m-d H:i:s');



try {
    $db->beginTransaction();

    // Initialize debug info
    $debugInfo = [];

    // 1. Update fg_warehouse table
    try {
        $sqlWarehouse = "UPDATE fg_warehouse SET status = 'done', pulled_at = :pulled_at WHERE id = :id";
        $updatedWarehouse = $db->Update($sqlWarehouse, [':id' => $id, ':pulled_at' => $pulled_at]);
        $debugInfo['fg_warehouse_updated'] = $updatedWarehouse;
    } catch (PDOException $e) {
        throw new Exception("Failed in fg_warehouse update: " . $e->getMessage());
    }

    // 2. Update delivery_form_new table
    try {
        $sqlDelivery = "UPDATE delivery_form_new SET status = 'done', section = 'WAREHOUSE' WHERE reference_no = :reference_no";
        $updatedDelivery = $db->Update($sqlDelivery, [':reference_no' => $reference_no]);
        $debugInfo['delivery_form_updated'] = $updatedDelivery;
    } catch (PDOException $e) {
        throw new Exception("Failed in delivery_form_new update: " . $e->getMessage());
    }

    // 3. Update assembly_list_new table
    try {
        $sqlAssembly = "UPDATE assembly_list_new SET status = 'done', section = 'warehouse' WHERE reference_no = :reference_no";
        $updatedAssembly = $db->Update($sqlAssembly, [':reference_no' => $reference_no]);
        $debugInfo['assembly_list_updated'] = $updatedAssembly;
    } catch (PDOException $e) {
        throw new Exception("Failed in assembly_list_new update: " . $e->getMessage());
    }

    // 4. Update material_inventory
    try {
        $sqlUpdateInventory = "UPDATE material_inventory 
            SET quantity = quantity + :total_quantity 
            WHERE material_no = :material_no 
            AND material_description = :material_description";

        $paramsUpdateInventory = [
            ':total_quantity' => $total_quantity,
            ':material_no' => $material_no,
            ':material_description' => $material_description,
        ];

        $updatedInventory = $db->Update($sqlUpdateInventory, $paramsUpdateInventory);
        $debugInfo['material_inventory_updated'] = $updatedInventory;
    } catch (PDOException $e) {
        throw new Exception("Failed in material_inventory update: " . $e->getMessage());
    }

    // ✅ Commit if all passed
    $db->commit();

    if ($updatedWarehouse  && $updatedAssembly && $updatedInventory) {
        echo json_encode(['success' => true, 'message' => 'Item marked as PULLED OUT']);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'No records were updated',
            'debug' => $debugInfo,
            'input' => $input
        ]);
    }

} catch (Exception $e) {
    $db->rollBack();
    echo json_encode([
        'success' => false,
        'message' => '❌ Error: ' . $e->getMessage(),
        'input' => $input,
        'debug' => $debugInfo ?? []
    ]);
}
