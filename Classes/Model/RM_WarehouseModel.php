<?php

namespace Model;

class RM_WarehouseModel
{
    private \DatabaseClass $db;

    public function __construct(\DatabaseClass $db)
    {
        $this->db = $db;
    }
    public function getIssuedComponents()
    {
        $sql = "
        SELECT 
            i.*, 
            ci.stage_name, ci.process_quantity,ci.usage_type,
            (
                SELECT JSON_ARRAYAGG(
                    JSON_OBJECT(
                        'material_no', r.material_no,
                        'material_description', r.material_description,
                        'usage', r.usage,
                        'component_name',r.component_name
                    )
                )
                FROM rawmaterials_inventory r
                WHERE r.components_material_no = i.material_no
            ) AS raw_materials
        FROM issued_rawmaterials i
        LEFT JOIN components_inventory ci 
            ON ci.material_no = i.material_no 
            AND ci.components_name = i.component_name
        WHERE i.delivered_at IS NULL
    ";

        return $this->db->Select($sql);
    }


    public function getIssuedHistory()
    {
        $sql = "
        SELECT 
            i.*, 
            ci.usage_type,
            (
                SELECT JSON_ARRAYAGG(
                    JSON_OBJECT(
                        'material_no', r.material_no,
                        'material_description', r.material_description,
                        'usage', r.usage
                    )
                )
                FROM rawmaterials_inventory r
                WHERE r.components_material_no = i.material_no
            ) AS raw_materials
        FROM issued_rawmaterials i
        LEFT JOIN components_inventory ci 
            ON ci.material_no = i.material_no 
            AND ci.components_name = i.component_name
        WHERE i.delivered_at IS NOT NULL
    ";
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

    public function getNextBatchNumber(): int
    {
        $sql = "SELECT MAX(batch) as max_batch 
            FROM stamping ";



        $result = $this->db->SelectOne($sql);

        return $result && $result['max_batch'] !== null ? (int)$result['max_batch'] : 0;
    }

    public function insertStampingStages(array $data, array $flattenedStages, int $existingCount, string $dateToday): bool|string
    {
        $sql = "INSERT INTO `stamping` 
        (`material_no`, `components_name`, `process_quantity`, `stage`, `stage_name`, `section`, 
        `total_quantity`, `pending_quantity`, `status`, `reference_no`, `created_at`, `batch`)
        VALUES 
        (:material_no, :components_name, :process_quantity, :stage, :stage_name, :section, 
        :total_quantity, :pending_quantity, :status, :reference_no, :created_at, :batch)";

        // Extract these first
        $processQty = (int) $data['process_quantity'];
        $totalQty   = $data['quantity'];
        $materialNo = $data['material_no'];
        $componentName = $data['component_name'];
        $createdAt = $data['created_at'];

        // Then get the batch
        $nextBatch = $this->getNextBatchNumber() + 1;

        error_log(print_r($flattenedStages, true));

        for ($i = 0; $i < $processQty; $i++) {
            if (!isset($flattenedStages[$i])) {
                return "Flattened stage index $i is missing";
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


    public function updateIssuedRawmaterials($id, $material_no, $component_name, $quantity)
    {
        $sql = "UPDATE issued_rawmaterials 
            SET delivered_at = NOW() ,rm_quantity =:quantity
            WHERE id = :id 
              AND material_no = :material_no 
              AND component_name = :component_name";

        $params = [
            ':id' => $id,
            ':material_no' => $material_no,
            ':component_name' => $component_name,
            ':quantity' => $quantity
        ];

        return $this->db->Update($sql, $params);
    }
}
