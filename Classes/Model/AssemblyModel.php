<?php

namespace Model;

class AssemblyModel
{
    private \DatabaseClass $db;

    public function __construct(\DatabaseClass $db)

    {
        $this->db = $db;
    }
    public function getAllAssemblyData()
    {
        $assembly = $this->db->Select("SELECT * FROM assembly_list WHERE time_out IS NOT NULL");
        $stamping = $this->db->Select("SELECT * FROM stamping WHERE section IN ('FINISHING','SPOT WELDING') AND time_out IS NOT NULL");

        return [
            'assembly' => $assembly,
            'stamping' => $stamping
        ];
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
    public function getTodoList()
    {
        $sql = "SELECT * FROM assembly_list WHERE  created_at >= DATE_SUB(NOW(), INTERVAL 2 DAY)";
        return $this->db->Select($sql);
    }
    public function markReworkAssemblyTimeIn(int $id, string $full_name, string $time_in)
    {
        if (empty($id) || empty($full_name)) {
            return "Missing ID or Full Name.";
        }

        $sql = "UPDATE rework_assembly 
                SET assembly_person_incharge = :full_name, 
                    assembly_timein = :time_in 
                WHERE id = :id";

        $params = [
            ':id' => $id,
            ':full_name' => $full_name,
            ':time_in' => $time_in
        ];

        $updated = $this->db->Update($sql, $params);

        return $updated ? true : "Failed to update rework_assembly.";
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

    public function deductComponentInventory(string $material_no, string $reference_no, int $total_qty, string $time_in): void
    {
        $sqlSelect = "SELECT id, components_name, usage_type, actual_inventory, 
                         critical, minimum, reorder, normal, maximum_inventory
                  FROM components_inventory 
                  WHERE material_no = :material_no";

        $components = $this->db->Select($sqlSelect, [':material_no' => $material_no]);

        if (!$components) {
            throw new \Exception("No components found for material_no: $material_no");
        }

        foreach ($components as $component) {
            $componentId = $component['id'];
            $componentName = $component['components_name'];
            $usageType = (int)$component['usage_type'];
            $currentInventory = (int)$component['actual_inventory'];

            $deductQty = $total_qty * $usageType;
            $newInventory = max(0, $currentInventory - $deductQty);

            // Update actual inventory
            $sqlUpdate = "UPDATE components_inventory 
                      SET actual_inventory = :new_inventory, updated_at = :date 
                      WHERE material_no = :material_no AND components_name = :components_name";

            $params = [
                ':new_inventory'    => $newInventory,
                ':material_no'      => $material_no,
                ':components_name'  => $componentName,
                ':date'             => $time_in
            ];

            $this->db->Update($sqlUpdate, $params);

            // ✅ Determine status after deduction
            $critical = (int)$component['critical'];
            $minimum = (int)$component['minimum'];
            $reorder = (int)$component['reorder'];
            $normal = (int)$component['normal'];
            $maximum = (int)$component['maximum_inventory'];

            if ($newInventory > $maximum) {
                $status = 'Maximum';
            } elseif ($newInventory >= $normal && $newInventory <= $maximum) {
                $status = 'Normal';
            } elseif ($newInventory >= $reorder && $newInventory < $normal) {
                $status = 'Reorder';
            } elseif ($newInventory >= $minimum && $newInventory < $reorder) {
                $status = 'Minimum';
            } elseif ($newInventory < $minimum) {
                $status = 'Reorder';
            }


            // 🚨 Only insert if status is Critical, Minimum, or Reorder
            if (in_array($status, ['Critical', 'Minimum', 'Reorder'])) {
                // Check if an issue for today already exists
                $checkSql = "SELECT COUNT(*) as count FROM issued_rawmaterials
                         WHERE material_no = :material_no
                         AND component_name = :component_name
                         AND DATE(issued_at) = CURDATE()";

                $checkParams = [
                    ':material_no'     => $material_no,
                    ':component_name'  => $componentName
                ];

                $existing = $this->db->SelectOne($checkSql, $checkParams);
                if (!$existing || (int)$existing['count'] === 0) {
                    // Insert issued_rawmaterials record using provided reference_no
                    $insertSql = "INSERT INTO issued_rawmaterials (
                    material_no, component_name, quantity, status, reference_no, issued_at
                  ) VALUES (
                     :material_no, :component_name, :quantity, :status, :reference_no, NOW()
                  )";

                    $insertParams = [

                        ':material_no'    => $material_no,
                        ':component_name' => $componentName,
                        ':quantity'       => $newInventory,
                        ':status'         => $status,
                        ':reference_no'   => $reference_no // 👈 using the one passed in
                    ];

                    $this->db->Insert($insertSql, $insertParams);
                }
            }
        }
    }
    public function updateReworkAssemblyTimeout(array $data): bool
    {
        $sql = "UPDATE rework_assembly 
        SET assembly_person_incharge = :full_name, 
            `replace` = :replace, 
            rework = :rework,
            assembly_pending_quantity = :assembly_pending_quantity,
            assembly_timeout = :time_out 
        WHERE id = :id";

        $params = [
            ':full_name' => $data['full_name'],
            ':id' => $data['id'],
            ':time_out' => $data['time_out'],
            ':replace' => $data['replace'],
            ':rework' => $data['rework'],
            ':assembly_pending_quantity' => $data['assembly_pending_quantity']
        ];

        return $this->db->Update($sql, $params);
    }
    public function getGroupedAssemblyByReference(string $reference_no): ?array
    {
        $sql = "SELECT 
                model,
                material_no,
                material_description,
                shift,
                lot_no,
                date_needed,
                SUM(`replace`) AS total_replace,
                SUM(`rework`) AS total_rework,
                SUM(`assembly_pending_quantity`) AS total_assembly_pending_quantity,
                MAX(quantity) AS total_quantity
            FROM rework_assembly
            WHERE reference_no = :reference_no
            GROUP BY reference_no, model, material_no, material_description, shift, lot_no, date_needed";

        $params = [':reference_no' => $reference_no];

        return $this->db->SelectOne($sql, $params);
    }
    public function updateComponentInventoryAfterReplace(string $material_no, int $total_replace, string $time_out, string $reference_no): void
    {
        // Step 1: Get components
        $sqlComponents = "SELECT id, components_name, usage_type, actual_inventory,
                             critical, minimum, reorder, normal, maximum_inventory
                      FROM components_inventory 
                      WHERE material_no = :material_no";

        $components = $this->db->Select($sqlComponents, [':material_no' => $material_no]);

        if (!$components) {
            throw new \Exception("No components found for material_no: $material_no");
        }

        // Step 2: Check if reference_no already exists in issued_rawmaterials
        $existsSql = "SELECT COUNT(*) as count FROM issued_rawmaterials WHERE reference_no = :reference_no";
        $existsResult = $this->db->SelectOne($existsSql, [':reference_no' => $reference_no]);

        $referenceExists = $existsResult && (int)$existsResult['count'] > 0;

        // Step 3: Update each component's inventory
        foreach ($components as $component) {
            $componentId = $component['id'];
            $componentsName = $component['components_name'];
            $usageType = (int)$component['usage_type'];
            $currentInventory = (int)$component['actual_inventory'];

            $returnQty = $total_replace * $usageType;
            $newInventory = $currentInventory - $returnQty;

            // Update inventory
            $sqlUpdateInventory = "UPDATE components_inventory 
                               SET actual_inventory = :new_inventory, updated_at = :date
                               WHERE material_no = :material_no AND components_name = :components_name";

            $paramsUpdateInventory = [
                ':new_inventory' => $newInventory,
                ':material_no' => $material_no,
                ':components_name' => $componentsName,
                ':date' => $time_out
            ];

            $this->db->Update($sqlUpdateInventory, $paramsUpdateInventory);

            // ✅ Determine new status
            $critical = (int)$component['critical'];
            $minimum = (int)$component['minimum'];
            $reorder = (int)$component['reorder'];
            $normal = (int)$component['normal'];
            $maximum = (int)$component['maximum_inventory'];

            if ($newInventory > $maximum) {
                $status = 'Maximum';
            } elseif ($newInventory >= $normal && $newInventory <= $maximum) {
                $status = 'Normal';
            } elseif ($newInventory >= $reorder && $newInventory < $normal) {
                $status = 'Reorder';
            } elseif ($newInventory >= $minimum && $newInventory < $reorder) {
                $status = 'Minimum';
            } elseif ($newInventory < $minimum) {
                $status = 'Critical';
            }

            // 🚨 Only insert if not existing AND status is Critical, Minimum, or Reorder
            if (!$referenceExists && in_array($status, ['Critical', 'Minimum', 'Reorder'])) {
                $insertSql = "INSERT INTO issued_rawmaterials (
                            material_no, component_name, quantity, status, reference_no, issued_at
                          ) VALUES (
                             :material_no, :component_name, :quantity, :status, :reference_no, NOW()
                          )";

                $insertParams = [

                    ':material_no' => $material_no,
                    ':component_name' => $componentsName,
                    ':quantity' => $newInventory,
                    ':status' => $status,
                    ':reference_no' => $reference_no
                ];

                $this->db->Insert($insertSql, $insertParams);
            }
        }
    }
    public function markReworkAssemblyAsDone(string $reference_no): void
    {
        $sql = "UPDATE rework_assembly 
            SET status = 'done' 
            WHERE reference_no = :reference_no";

        $this->db->Update($sql, [':reference_no' => $reference_no]);
    }
    public function insertReworkQC(string $reference_no, array $result, int $total, string $time_out): void
    {
        $sql = "INSERT INTO rework_qc (
                reference_no, model, material_no, material_description,
                shift, lot_no, quantity,
                qc_quantity, qc_person_incharge,
                qc_timein, qc_timeout,
                status, section, date_needed, created_at
            ) VALUES (
                :reference_no, :model, :material_no, :material_description,
                :shift, :lot_no, :quantity,
                :qc_quantity, :qc_person_incharge,
                :qc_timein, :qc_timeout,
                :status, :section, :date_needed, :created_at
            )";

        $params = [
            ':reference_no' => $reference_no,
            ':model' => $result['model'],
            ':material_no' => $result['material_no'],
            ':material_description' => $result['material_description'],
            ':shift' => $result['shift'],
            ':lot_no' => $result['lot_no'],
            ':quantity' => $total,
            ':qc_quantity' => $total,
            ':qc_person_incharge' => null,
            ':qc_timein' => null,
            ':qc_timeout' => null,
            ':status' => 'pending',
            ':section' => 'qc',
            ':date_needed' => $result['date_needed'],
            ':created_at' => $time_out,
        ];

        $this->db->Insert($sql, $params);
    }
    public function duplicateReworkAssembly(int $id, int $replace, int $rework, int $inputQty, string $time_out): int
    {
        $selectSql = "SELECT * FROM rework_assembly WHERE id = :id";
        $selectParams = [':id' => $id];

        $row = $this->db->SelectOne($selectSql, $selectParams);

        if (!$row) {
            throw new \Exception("No record found to duplicate for ID: $id");
        }

        $newData = [
            ':itemID' => $id,
            ':reference_no' => $row['reference_no'],
            ':model' => $row['model'],
            ':material_no' => $row['material_no'],
            ':material_description' => $row['material_description'],
            ':shift' => $row['shift'],
            ':lot_no' => $row['lot_no'],
            ':replace' => null,
            ':rework' => null,
            ':quantity' => $row['quantity'],
            ':assembly_quantity' => $row['assembly_pending_quantity'],
            ':assembly_pending_quantity' => $row['assembly_pending_quantity'],
            ':assembly_person_incharge' => null,
            ':assembly_timein' => null,
            ':assembly_timeout' => null,
            ':status' => 'continue',
            ':section' => 'assembly',
            ':date_needed' => $row['date_needed'],
            ':created_at' => $time_out,
        ];

        $insertSql = "INSERT INTO rework_assembly (
        itemID,
        reference_no, model, material_no, material_description,
        shift, lot_no, `replace`, rework, quantity,
        assembly_quantity, assembly_pending_quantity, assembly_person_incharge,
        assembly_timein, assembly_timeout,
        status, section, date_needed, created_at
    ) VALUES (
        :itemID, :reference_no, :model, :material_no, :material_description,
        :shift, :lot_no, :replace, :rework, :quantity,
        :assembly_quantity, :assembly_pending_quantity, :assembly_person_incharge,
        :assembly_timein, :assembly_timeout,
        :status, :section, :date_needed, :created_at
    )";

        return $this->db->Insert($insertSql, $newData); // returns inserted row count
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





    public function getAllUsers(): array
    {
        return $this->db->Select("SELECT * FROM users");
    }
}
