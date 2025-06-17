<?php

namespace Model;

class RM_WarehouseModel
{
    private \DatabaseClass $db;

    public function __construct(\DatabaseClass $db)
    {
        $this->db = $db;
    }
    public function getComponents()
    {
        $sql = "SELECT * FROM components_inventory WHERE actual_inventory < normal;";
        return $this->db->Select($sql);
    }
    public function getIssued()
    {
        $sql = " SELECT * FROM rm_warehouse WHERE created_at >= CURDATE();";
        return $this->db->Select($sql);
    }
    public function getIssuedHistory()
    {
        $sql = "SELECT * FROM rm_warehouse";
        return $this->db->Select($sql);
    }
    public function updateComponentInventoryStatus(string $material_no, string $component_name, int $rm_stocks): bool
    {
        $sql = "UPDATE `components_inventory` 
            SET status = :status, section = :section, rm_stocks = :rm_stocks
            WHERE material_no = :material_no AND components_name = :components_name";

        $params = [
            ':status' => 'done',
            ':section' => 'stamping',
            ':rm_stocks' => $rm_stocks,
            ':material_no' => $material_no,
            ':components_name' => $component_name
        ];

        return $this->db->Update($sql, $params);
    }
    public function insertIntoRMWarehouse(array $data): bool
    {
        $sql = "INSERT INTO `rm_warehouse` 
            (`material_no`, `component_name`, `process_quantity`, `quantity`, `status`, `created_at`, `reference_no`) 
            VALUES 
            (:material_no, :component_name, :process_quantity, :quantity, :status, :created_at, :reference_no)";

        $params = [
            ':material_no' => $data['material_no'],
            ':component_name' => $data['component_name'],
            ':process_quantity' => $data['process_quantity'],
            ':quantity' => $data['quantity'],
            ':status' => 'pending',
            ':created_at' => $data['created_at'],
            ':reference_no' => $data['reference_no']
        ];

        return $this->db->Update($sql, $params);
    }
    public function getNextStampingBatch(string $material_no, string $component_name): int
    {
        $sql = "SELECT MAX(batch) as last_batch FROM stamping WHERE material_no = :material_no AND components_name = :components_name";
        $result = $this->db->SelectOne($sql, [
            ':material_no' => $material_no,
            ':components_name' => $component_name
        ]);

        return ($result && $result['last_batch']) ? ((int)$result['last_batch'] + 1) : 1;
    }

    public function insertStampingStages(array $data, array $flattenedStages, int $existingCount, string $dateToday, int $nextBatch): bool
    {
        $sql = "INSERT INTO `stamping` 
        (`material_no`, `components_name`, `process_quantity`, `stage`, `stage_name`, `section`, 
         `total_quantity`, `pending_quantity`, `status`, `reference_no`, `created_at`, `batch`)
        VALUES 
        (:material_no, :components_name, :process_quantity, :stage, :stage_name, :section, 
         :total_quantity, :pending_quantity, :status, :reference_no, :created_at, :batch)";

        $processQty = (int) $data['process_quantity'];
        $totalQty   = $data['quantity'];
        $materialNo = $data['material_no'];
        $componentName = $data['component_name'];
        $createdAt = $data['created_at'];

        for ($i = 0; $i < $processQty; $i++) {
            // Prevent index error if stages are fewer than expected
            if (!isset($flattenedStages[$i])) {
                return false;
            }

            $stageIndex = $i + 1;
            $referenceNo = $dateToday . '-' . str_pad($existingCount + $stageIndex, 4, '0', STR_PAD_LEFT);

            $params = [
                ':material_no'       => $materialNo,
                ':components_name'   => $componentName,
                ':process_quantity'  => $processQty,
                ':stage'             => $stageIndex,
                ':stage_name'        => $flattenedStages[$i]['stage_name'],
                ':section'           => $flattenedStages[$i]['section'],
                ':total_quantity'    => $totalQty,
                ':pending_quantity'  => $totalQty,
                ':status'            => 'pending',
                ':reference_no'      => $referenceNo,
                ':created_at'        => $createdAt,
                ':batch'             => $nextBatch
            ];

            if (!$this->db->Insert($sql, $params)) {
                return false;
            }
        }

        return true;
    }
}
