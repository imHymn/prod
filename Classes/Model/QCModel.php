<?php

namespace Model;

class QCModel
{
    private \DatabaseClass $db;

    public function __construct(\DatabaseClass $db)
    {
        $this->db = $db;
    }
    public function getManpowerReworkData()
    {
        $sql = "SELECT * FROM rework_qc WHERE qc_timeout IS NOT NULL";
        return $this->db->Select($sql);
    }
    public function getQCData()
    {
        $sql = "SELECT * FROM qc_list";
        return $this->db->Select($sql);
    }
    public function getRework()
    {
        $sql = "SELECT * FROM rework_qc WHERE section = 'qc' AND status IN ('pending','continue') AND created_at >= DATE_SUB(NOW(), INTERVAL 2 DAY)";
        return $this->db->Select($sql);
    }
    public function getReworkData()
    {
        $sql = "SELECT * FROM rework_qcWHERE status = 'done'";
        return $this->db->Select($sql);
    }
    public function getTodoList()
    {
        $sql = "SELECT * FROM qc_list WHERE status = 'pending' AND section ='qc' AND created_at >= DATE_SUB(NOW(), INTERVAL 2 DAY)";
        return $this->db->Select($sql);
    }
    public function qcMonitoring()
    {
        $sql = "SELECT * FROM `assembly_list` WHERE status_qc ='done'";
        return $this->db->Select($sql);
    }
    public function updateQCPersonIncharge(int $id, string $name, string $time_in): bool
    {
        $sql = "UPDATE qc_list SET person_incharge = :name, time_in = :time_in WHERE id = :id";
        $params = [
            ':id'      => $id,
            ':name'    => $name,
            ':time_in' => $time_in,
        ];

        return $this->db->Update($sql, $params);
    }
    public function updateQCListTimeout(array $data): bool
    {
        $sql = "UPDATE qc_list 
            SET 
                done_quantity = :quantity,
                pending_quantity = :pending_quantity,
                good = :good,
                no_good = :no_good,
                rework = :rework,
                `replace` = :replace,
                time_out = :time_out,
                person_incharge = :name
            WHERE id = :id";

        $params = [
            ':id' => $data['id'],
            ':pending_quantity' => $data['pending_quantity'],
            ':quantity' => $data['quantity'],
            ':good' => $data['good'],
            ':no_good' => $data['no_good'],
            ':rework' => $data['rework'],
            ':replace' => $data['replace'],
            ':time_out' => $data['time_out'],
            ':name' => $data['name'],
        ];

        return $this->db->Update($sql, $params);
    }
    public function getQCTotalSummary(string $reference_no): ?array
    {
        $sql = "SELECT 
                SUM(done_quantity) AS total_done, 
                SUM(good) AS total_good,
                SUM(no_good) AS total_no_good,
                SUM(rework) AS total_rework,
                SUM(`replace`) AS total_replace, 
                MAX(total_quantity) AS total_required 
            FROM qc_list 
            WHERE reference_no = :reference_no";

        $params = [':reference_no' => $reference_no];

        return $this->db->SelectOne($sql, $params);
    }
    public function insertReworkAssembly(array $data): int
    {
        $sql = "INSERT INTO rework_assembly
                (itemID, model, material_no, material_description, shift, lot_no, `replace`, rework, quantity, assembly_quantity, date_needed, reference_no, created_at, status, section)
            VALUES 
                (:itemID, :model, :material_no, :material_description, :shift, :lot_no, :replace, :rework, :quantity, :assembly_quantity, :date_needed, :reference_no, :created_at, :status, :section)";

        return $this->db->Insert($sql, [
            ':itemID' => $data['id'],
            ':model' => $data['model'],
            ':material_no' => $data['material_no'],
            ':material_description' => $data['material_description'],
            ':shift' => $data['shift'],
            ':lot_no' => $data['lot_no'],
            ':replace' => $data['total_replace'],
            ':rework' => $data['total_rework'],
            ':quantity' => $data['total_no_good'],
            ':assembly_quantity' => $data['total_no_good'],
            ':date_needed' => $data['date_needed'],
            ':reference_no' => $data['reference_no'],
            ':created_at' => $data['time_out'],
            ':status' => 'pending',
            ':section' => 'assembly'
        ]);
    }
    public function moveToFGWarehouse(array $data): bool
    {
        // Insert into fg_warehouse
        $insertFG = "INSERT INTO fg_warehouse (
        reference_no, material_no, material_description, model, quantity, total_quantity,
        lot_no, shift, date_needed, section, status, created_at
    ) VALUES (
        :reference_no, :material_no, :material_description, :model, :quantity, :total_quantity,
        :lot_no, :shift, :date_needed, :section, :status, :created_at
    )";

        $paramsFG = [
            ':reference_no' => $data['reference_no'],
            ':material_no' => $data['material_no'],
            ':material_description' => $data['material_description'],
            ':model' => $data['model'],
            ':quantity' => $data['total_good'],
            ':total_quantity' => $data['total_quantity'],
            ':lot_no' => $data['lot_no'],
            ':shift' => $data['shift'],
            ':date_needed' => $data['date_needed'],
            ':section' => 'warehouse',
            ':status' => 'pending',
            ':created_at' => $data['created_at'],
        ];

        $this->db->Insert($insertFG, $paramsFG);

        // Update delivery_form
        $sqlUpdateDelivery = "UPDATE delivery_form 
                          SET section = :newSection 
                          WHERE reference_no = :reference_no";

        $paramsDelivery = [
            ':reference_no' => $data['reference_no'],
            ':newSection' => $data['new_section']
        ];
        $this->db->Update($sqlUpdateDelivery, $paramsDelivery);

        // Update assembly_list
        $sqlUpdateAssembly = "UPDATE assembly_list 
                          SET status = :newStatus, section = :newSection 
                          WHERE reference_no = :reference_no";

        $paramsAssembly = [
            ':reference_no' => $data['reference_no'],
            ':newSection' => $data['new_section'],
            ':newStatus' => $data['new_status']
        ];
        $this->db->Update($sqlUpdateAssembly, $paramsAssembly);

        // Update qc_list
        $sqlUpdateQC = "UPDATE qc_list 
                    SET status = :newStatus, section = :newSection 
                    WHERE reference_no = :reference_no";

        $paramsQC = [
            ':reference_no' => $data['reference_no'],
            ':newSection' => $data['new_section'],
            ':newStatus' => 'done'
        ];
        $this->db->Update($sqlUpdateQC, $paramsQC);

        return true;
    }
    public function duplicatePendingQCRow(int $id, int $pending_quantity, string $time_out): bool
    {
        $selectSql = "SELECT * FROM qc_list WHERE id = :id";
        $selectParams = [':id' => $id];

        $modifyCallback = function ($row) use ($id, $pending_quantity, $time_out) {
            return [
                'itemID' => $id,
                'model' => $row['model'],
                'material_no' => $row['material_no'],
                'material_description' => $row['material_description'],
                'reference_no' => $row['reference_no'],
                'shift' => $row['shift'],
                'lot_no' => $row['lot_no'],
                'pending_quantity' => $pending_quantity,
                'total_quantity' => $row['total_quantity'],
                'status' => $row['status'],
                'section' => $row['section'],
                'date_needed' => $row['date_needed'],
                'created_at' => $time_out,
            ];
        };

        $insertSql = "INSERT INTO qc_list (
        itemID, model, material_no, material_description, pending_quantity, reference_no, 
        shift, lot_no, total_quantity, status, section, date_needed, created_at
    ) VALUES (
        :itemID, :model, :material_no, :material_description, :pending_quantity, :reference_no, 
        :shift, :lot_no, :total_quantity, :status, :section, :date_needed, :created_at
    )";

        return $this->db->DuplicateAndModify($selectSql, $selectParams, $modifyCallback, $insertSql);
    }
}
