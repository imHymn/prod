<?php

namespace Model;

class StampingModel
{
    private \DatabaseClass $db;

    public function __construct(\DatabaseClass $db)
    {
        $this->db = $db;
    }
    public function getComponents()
    {
        return $this->db->Select("SELECT * FROM `components_inventory`");
    }
    public function getManpowerData()
    {
        return $this->db->Select("SELECT * FROM `stamping` WHERE status ='done'");
    }
    public function getWorkLogs()
    {
        return $this->db->Select("SELECT * FROM `stamping` WHERE status ='done'");
    }
    public function getTodoListAllSection()
    {
        return $this->db->Select("SELECT * FROM stamping WHERE created_at >= DATE_SUB(NOW(), INTERVAL 2 DAY)");
    }
    public function getTodoListSpecificSection($section)
    {
        $normalizedSection = str_replace('-', ' ', $section);
        $sql = "SELECT * FROM stamping WHERE REPLACE(section, '-', ' ') = :section AND created_at >= DATE_SUB(NOW(), INTERVAL 2 DAY)";
        return $this->db->Select($sql, [':section' => $normalizedSection]);
    }
    public function fetchStageStatus($data)
    {
        $sql = "SELECT stage_name,section,stage, status FROM stamping 
                WHERE material_no = :material_no 
                AND components_name = :components_name AND batch=:batch
                ORDER BY stage ASC";
        $params = [
            ':material_no' => $data['material_no'],
            ':components_name' => $data['components_name'],
            ':batch' => $data['batch']
        ];
        return $this->db->Select($sql, $params);
    }
    public function startProcessingStage(array $data): bool
    {
        $sql = "UPDATE `stamping`
                SET person_incharge = :name,
                    time_in = :timein,
                    status = :status
                WHERE id = :id";

        $params = [
            ':name'   => $data['name'],
            ':timein' => date('Y-m-d H:i:s'),  // You can also pass it in from the controller if preferred
            ':status' => 'ongoing',
            ':id'     => $data['id']
        ];

        return $this->db->Update($sql, $params);
    }
    public function updateStampingTimeout(array $data): bool
    {
        $pendingQuantity = ($data['pending_quantity'] > 0)
            ? $data['pending_quantity'] - $data['inputQuantity']
            : $data['total_quantity'] - $data['inputQuantity'];

        $timeout = date('Y-m-d H:i:s');

        $sql = "UPDATE `stamping` 
                SET person_incharge=:name, time_out=:timeout, status='done', 
                    quantity=:inputQuantity, pending_quantity=:pending_quantity, updated_at=:updated_at 
                WHERE id=:id";

        $params = [
            ':name' => $data['name'],
            ':timeout' => $timeout,
            ':id' => $data['id'],
            ':inputQuantity' => $data['inputQuantity'],
            ':pending_quantity' => $pendingQuantity,
            ':updated_at' => $timeout
        ];

        return $this->db->Update($sql, $params);
    }

    public function getStampingById(int $id): ?array
    {
        return $this->db->SelectOne("SELECT * FROM stamping WHERE id = :id", [':id' => $id]);
    }

    public function getQuantityStats(string $referenceNo): ?array
    {
        $sql = "
            SELECT SUM(quantity) as total_quantity_done, MAX(total_quantity) as max_total_quantity
            FROM stamping
            WHERE reference_no = :reference_no
        ";
        return $this->db->SelectOne($sql, [':reference_no' => $referenceNo]);
    }

    public function duplicateIfNotDone(array $row, int $inputQuantity): int
    {
        $modifyCallback = function ($r) use ($inputQuantity) {
            return [
                'reference_no' => $r['reference_no'],
                'material_no' => $r['material_no'],
                'components_name' => $r['components_name'],
                'process_quantity' => $r['process_quantity'],
                'total_quantity' => $r['total_quantity'],
                'pending_quantity' => $r['pending_quantity'],
                'stage' => $r['stage'],
                'stage_name' => $r['stage_name'],
                'section' => $r['section'],
                'batch' => $r['batch'],
                'time_in' => null,
                'time_out' => null,
                'status' => 'pending',
                'person_incharge' => null,
                'created_at' => $r['created_at'],
                'updated_at' => null
            ];
        };

        $insertSql = "INSERT INTO stamping (
            reference_no, material_no, components_name, process_quantity, total_quantity, pending_quantity,
            stage, stage_name, section, batch, time_in, time_out, status,
            person_incharge, created_at, updated_at
        ) VALUES (
            :reference_no, :material_no, :components_name, :process_quantity, :total_quantity, :pending_quantity,
            :stage, :stage_name, :section, :batch, :time_in, :time_out, :status,
            :person_incharge, :created_at, :updated_at
        )";

        return $this->db->DuplicateAndModify("SELECT * FROM stamping WHERE id = :id", [':id' => $row['id']], $modifyCallback, $insertSql);
    }

    public function areAllStagesDone(string $materialNo, string $componentName, int $processQty, int $totalQty, int $batch): bool
    {
        for ($stage = 1; $stage <= $processQty; $stage++) {
            $sql = "SELECT SUM(quantity) as total_stage_quantity
                    FROM stamping
                    WHERE material_no = :material_no AND components_name = :component_name 
                    AND stage = :stage AND batch = :batch";

            $result = $this->db->SelectOne($sql, [
                ':material_no' => $materialNo,
                ':component_name' => $componentName,
                ':stage' => $stage,
                ':batch' => $batch
            ]);

            if ((int)($result['total_stage_quantity'] ?? 0) < $totalQty) {
                return false;
            }
        }
        return true;
    }

    public function updateInventoryAndWarehouse(string $materialNo, string $componentName, int $quantity): void
    {
        $this->db->Update("
            UPDATE components_inventory 
            SET actual_inventory = actual_inventory + :quantity, rm_stocks = 0
            WHERE material_no = :material_no AND components_name = :component_name
        ", [
            ':quantity' => $quantity,
            ':material_no' => $materialNo,
            ':component_name' => $componentName
        ]);

        $this->db->Update("
            UPDATE rm_warehouse 
            SET status = 'done'
            WHERE material_no = :material_no AND component_name = :component_name
        ", [
            ':material_no' => $materialNo,
            ':component_name' => $componentName
        ]);
    }
}
