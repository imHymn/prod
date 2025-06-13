<?php

namespace Model;

class AssemblyModel
{
    private \DatabaseClass $db;

    public function __construct(\DatabaseClass $db)

    {
        $this->db = $db;
    }

    public function getAllUsers(): array
    {
        return $this->db->Select("SELECT * FROM users");
    }

    public function getAllAssemblyData()
    {
        $sql = "SELECT * FROM assembly_list";
        return $this->db->Select($sql);
    }
    public function getManpowerRework()
    {
        $sql = "SELECT * FROM rework_assembly WHERE assembly_timeout IS NOT NULL";
        return $this->db->Select($sql);
    }
    public function getReworkData()
    {
        $sql = "SELECT * FROM rework_assembly WHERE section = 'assembly' AND status IN ('pending','continue') AND created_at >= DATE_SUB(NOW(), INTERVAL 2 DAY)";
        return $this->db->Select($sql);
    }
    public function getSpecificComponent(int $materialId)
    {
        $sql = "SELECT * FROM `components_inventory` WHERE material_no ='$materialId'";
        return $this->db->Select($sql);
    }

    public function updateDeliveryFormSection(int $id): int
    {
        $sql = "UPDATE delivery_form SET section = 'ASSEMBLY' WHERE id = :id";
        return $this->db->Update($sql, [':id' => $id]);
    }

    public function getLatestPendingQuantity(string $reference_no): int
    {
        $sql = "SELECT pending_quantity 
                FROM assembly_list 
                WHERE reference_no = :reference_no 
                ORDER BY time_in DESC 
                LIMIT 1";

        $params = [':reference_no' => $reference_no];
        $result = $this->db->SelectOne($sql, $params);

        return isset($result['pending_quantity']) ? (int)$result['pending_quantity'] : 0;
    }
    public function insertAssemblyRecord(array $data): int
    {
        $sql = "INSERT INTO assembly_list
            (itemID, model, shift, lot_no, date_needed, reference_no, material_no, material_description, pending_quantity, total_quantity, person_incharge, time_in, status, section, created_at)
            VALUES 
            (:itemID, :model, :shift, :lot_no, :date_needed, :reference_no, :material_no, :material_description, :pending_quantity, :total_qty, :person_incharge, :time_in, :status, :section, :created_at)";

        return $this->db->Insert($sql, [
            ':itemID'               => $data['itemID'],
            ':model'                => $data['model'],
            ':shift'                => $data['shift'],
            ':lot_no'               => $data['lot_no'],
            ':date_needed'         => $data['date_needed'],
            ':reference_no'        => $data['reference_no'],
            ':material_no'         => $data['material_no'],
            ':material_description' => $data['material_description'],
            ':pending_quantity'    => $data['pending_quantity'],
            ':total_qty'           => $data['total_qty'],
            ':person_incharge'     => $data['person_incharge'],
            ':time_in'             => $data['time_in'],
            ':status'              => $data['status'],
            ':section'             => $data['section'],
            ':created_at'          => $data['time_in'], // Reused timestamp
        ]);
    }
    public function deductComponentInventory(int $material_no, int $total_qty): void
    {
        $sqlSelect = "SELECT components_name, usage_type, actual_inventory 
                  FROM components_inventory 
                  WHERE material_no = :material_no";

        $components = $this->db->Select($sqlSelect, [':material_no' => $material_no]);

        if (!$components) {
            throw new \Exception("No components found for material_no: $material_no");
        }

        foreach ($components as $component) {
            $componentName = $component['components_name'];
            $usageType = (int)$component['usage_type'];
            $currentInventory = (int)$component['actual_inventory'];

            $deductQty = $total_qty * $usageType;
            $newInventory = max(0, $currentInventory - $deductQty);

            $sqlUpdate = "UPDATE components_inventory 
                      SET actual_inventory = :new_inventory 
                      WHERE material_no = :material_no AND components_name = :components_name";

            $params = [
                ':new_inventory'    => $newInventory,
                ':material_no'      => $material_no,
                ':components_name'  => $componentName,
            ];

            $this->db->Update($sqlUpdate, $params);
        }
    }

    public function updateAssemblyListTimeout(
        int $done_quantity,
        int $pending_quantity,
        string $itemID,
        string $time_out,
        string $status,
        string $section
    ): bool {
        $sqlUpdate = "UPDATE assembly_list SET 
                        done_quantity = :done_quantity,
                        pending_quantity = :pending_quantity,
                        status = :status,
                        section = :section,
                        time_out = :time_out
                    WHERE itemID = :itemID";

        $paramsUpdate = [
            ':done_quantity' => $done_quantity,
            ':pending_quantity' => $pending_quantity,
            ':itemID' => $itemID,
            ':time_out' => $time_out,
            ':status' => $status,
            ':section' => $section,
        ];

        return $this->db->Update($sqlUpdate, $paramsUpdate);
    }

    public function getPendingAssembly($id)
    {
        $sql = "SELECT assembly_pending, total_quantity
                        FROM delivery_form 
                        WHERE id = :id 
                        ORDER BY created_at DESC 
                        LIMIT 1";
        return $this->db->SelectOne($sql, [':id' => $id]);
    }
    public function updateDeliveryFormPending($id, $remainingPending)
    {
        $sql = "UPDATE delivery_form SET section = :section, status = :status, assembly_pending = :remainingPending WHERE id = :id";
        $params = [
            ':section' => 'QC',
            ':status' => 'done',
            ':remainingPending' => $remainingPending,
            ':id' => $id,
        ];

        return $this->db->Update($sql, $params);
    }
    public function getTotalDoneAndRequired(string $reference_no): ?array
    {
        $sql = "SELECT SUM(done_quantity) AS total_done, MAX(total_quantity) AS total_required FROM assembly_list WHERE reference_no = :reference_no";
        return $this->db->SelectOne($sql, [':reference_no' => $reference_no]);
    }
    public function moveToQCList(array $data): ?int
    {
        // 1. Insert into `qc_list`
        $sqlInsert = "INSERT INTO qc_list
        (model, shift, lot_no, date_needed, reference_no, material_no, material_description, total_quantity, status, section, created_at)
        VALUES 
        (:model, :shift, :lot_no, :date_needed, :reference_no, :material_no, :material_description, :total_quantity, :status, :section, :created_at)";

        $paramsInsert = [
            ':model' => $data['model'],
            ':shift' => $data['shift'],
            ':lot_no' => $data['lot_no'],
            ':date_needed' => $data['date_needed'],
            ':reference_no' => $data['reference_no'],
            ':material_no' => $data['material_no'],
            ':material_description' => $data['material_description'],
            ':total_quantity' => $data['total_quantity'],
            ':status' => 'pending',
            ':section' => 'qc',
            ':created_at' => $data['created_at']
        ];

        $insertedId = $this->db->Insert($sqlInsert, $paramsInsert);

        if (!$insertedId) return null;

        $sqlUpdateDelivery = "UPDATE delivery_form 
                          SET section = 'QC' 
                          WHERE reference_no = :reference_no";

        $this->db->Update($sqlUpdateDelivery, [':reference_no' => $data['reference_no']]);

        $sqlUpdateAssembly = "UPDATE assembly_list 
                          SET status = 'done', section = 'qc' 
                          WHERE reference_no = :reference_no";

        $this->db->Update($sqlUpdateAssembly, [':reference_no' => $data['reference_no']]);

        return $insertedId;
    }
    public function duplicateDeliveryFormWithPendingUpdate(int $id, string $time_out, int $remainingPending): bool
    {
        $selectSql = "SELECT * FROM delivery_form WHERE id = :id";
        $row = $this->db->SelectOne($selectSql, [':id' => $id]);

        if (!$row) {
            return false;
        }

        $newRow = [
            'material_no'        => $row['material_no'],
            'material_description' => $row['material_description'],
            'model_name'         => $row['model_name'],
            'quantity'           => $row['quantity'],
            'total_quantity'     => $row['total_quantity'],
            'assembly_pending'   => $remainingPending,
            'supplement_order'   => $row['supplement_order'],
            'date_needed'        => $row['date_needed'],
            'lot_no'             => $row['lot_no'],
            'reference_no'       => $row['reference_no'],
            'shift'              => $row['shift'],
            'status'             => 'continue',
            'section'            => 'ASSEMBLY',
            'created_at'         => $time_out,
        ];

        $insertSql = "INSERT INTO delivery_form (
        reference_no, material_no, material_description, model_name,
        quantity, total_quantity, assembly_pending, supplement_order,
        date_needed, lot_no, shift, status, section, created_at
    ) VALUES (
        :reference_no, :material_no, :material_description, :model_name,
        :quantity, :total_quantity, :assembly_pending, :supplement_order,
        :date_needed, :lot_no, :shift, :status, :section, :created_at
    )";

        return $this->db->Insert($insertSql, $newRow) !== false;
    }
}
