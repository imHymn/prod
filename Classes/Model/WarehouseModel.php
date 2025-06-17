<?php

namespace Model;

class WarehouseModel
{
    private \DatabaseClass $db;

    public function __construct(\DatabaseClass $db)
    {
        $this->db = $db;
    }
    public function getFGWarehouse()
    {
        $sql = "SELECT * from fg_warehouse";
        return $this->db->Select($sql);
    }
    public function getPendingPulling()
    {
        $sql = "SELECT * from fg_warehouse WHERE status='pending'";
        return $this->db->Select($sql);
    }
    public function getPullingHistory()
    {
        $sql = "SELECT * from fg_warehouse WHERE status='done'";
        return $this->db->Select($sql);
    }
    public function getStockWarehouse()
    {
        $sql = "SELECT * from material_inventory";
        return $this->db->Select($sql);
    }
    public function markAsPulledFromFG($id, $pulled_at)
    {
        $sql = "UPDATE fg_warehouse SET status = 'done', pulled_at = :pulled_at WHERE id = :id";
        $result = $this->db->Update($sql, [':id' => $id, ':pulled_at' => $pulled_at]);

        return $result ? true : "❌ Failed to update FG Warehouse.";
    }


    public function markDeliveryFormAsDone($reference_no)
    {
        $sql = "UPDATE delivery_form SET status = 'done', section = 'WAREHOUSE' WHERE reference_no = :reference_no";
        $result = $this->db->Update($sql, [':reference_no' => $reference_no]);

        return $result ? true : "❌ Failed to update Delivery Form.";
    }


    public function markAssemblyListAsDone($reference_no)
    {
        $sql = "UPDATE assembly_list SET status = 'done', section = 'warehouse' WHERE reference_no = :reference_no";
        $result = $this->db->Update($sql, [':reference_no' => $reference_no]);

        return $result ? true : "❌ Failed to update Assembly List.";
    }

    public function updateMaterialInventory($material_no, $material_description, $quantity)
    {

        $sql = "UPDATE material_inventory 
            SET quantity = quantity + :quantity 
            WHERE material_no = :material_no 
            AND material_description = :material_description";

        $params = [
            ':quantity' => $quantity,
            ':material_no' => $material_no,
            ':material_description' => $material_description,
        ];

        $result = $this->db->Update($sql, $params);

        return $result ? true : "❌ Failed to update Material Inventory.";
    }
}
