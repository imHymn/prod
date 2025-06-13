<?php

namespace Model;

class DeliveryModel
{
    private \DatabaseClass $db;

    public function __construct(\DatabaseClass $db)
    {
        $this->db = $db;
    }

    public function getAllCustomers(): array
    {
        return $this->db->Select("SELECT * FROM delivery_form WHERE created_at >= DATE_SUB(NOW(), INTERVAL 2 DAY)");
    }

    public function getDistinctCustomerNames(): array
    {
        return $this->db->Select("SELECT DISTINCT customer_name FROM material_inventory");
    }

    public function getLatestLotNoByModel(string $modelName): array
    {
        $sql = "SELECT lot_no FROM delivery_form WHERE model_name = :model_name ORDER BY lot_no DESC LIMIT 1";
        return $this->db->Select($sql, [':model_name' => $modelName]);
    }

    public function getPulledOut(): array
    {
        $sql = "SELECT * FROM delivery_form WHERE (status = 'pending' OR status = 'continue') 
                AND (section = 'ASSEMBLY' OR section = 'DELIVERY')
                AND created_at >= DATE_SUB(NOW(), INTERVAL 2 DAY)";
        return $this->db->Select($sql);
    }

    public function selectDistinctModelName(string $customerName): array
    {
        $sql = "SELECT DISTINCT model_name FROM material_inventory WHERE customer_name = :customer_name";
        return $this->db->Select($sql, ['customer_name' => $customerName]);
    }

    public function getSKU(string $customerName, string $modelName): array
    {
        $sql = "SELECT material_description, material_no FROM material_inventory WHERE customer_name = :customer_name AND model_name = :model_name";
        return $this->db->Select($sql, ['customer_name' => $customerName, 'model_name' => $modelName]);
    }

    public function getNextLotNumber(string $modelName): int
    {
        $sql = "SELECT lot_no FROM delivery_form WHERE model_name = :model_name ORDER BY lot_no DESC LIMIT 1";
        $result = $this->db->Select($sql, [':model_name' => $modelName]);

        if (!empty($result)) {
            return (int)$result[0]['lot_no'] + 1;
        } else {
            return 1;
        }
    }

    public function getMaterialStock(array $input): array
    {
        $insufficientStockItems = []; // initialize array

        foreach ($input as $item) {
            $invCheckSql = "SELECT quantity FROM material_inventory 
                        WHERE material_no = :material_no 
                        AND material_description = :material_description 
                        AND model_name = :model_name 
                        LIMIT 1";
            $invParams = [
                ':material_no' => $item['material_no'],
                ':material_description' => $item['material_description'],
                ':model_name' => $item['model_name']
            ];

            $inventory = $this->db->Select($invCheckSql, $invParams); // fixed db access

            if (empty($inventory)) {
                $insufficientStockItems[] = [
                    'material_no' => $item['material_no'],
                    'material_description' => $item['material_description'],
                    'reason' => 'Material not found in inventory'
                ];
                continue;
            }

            $currentInventory = (int)$inventory[0]['quantity'];
            $requiredQty = (int)$item['total_quantity'];

            if ($currentInventory < $requiredQty) {
                $insufficientStockItems[] = [
                    'material_no' => $item['material_no'],
                    'material_description' => $item['material_description'],
                    'reason' => "Insufficient stock (Available: $currentInventory, Needed: $requiredQty)"
                ];
            }
        }

        return $insufficientStockItems; // important: return the result
    }

    public function selectReferenceNo(string $today): string
    {

        $sql = "SELECT reference_no FROM delivery_form WHERE reference_no LIKE :today_pattern ORDER BY reference_no DESC LIMIT 1";
        $result = $this->db->Select($sql, [':today_pattern' => $today . '-%']);

        if (!empty($result)) {
            $lastRef = $result[0]['reference_no'];
            $lastNumber = (int)substr($lastRef, -4); // Extract the last 4 digits
        } else {
            $lastNumber = 0;
        }

        $nextRef = $today . '-' . str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        return $nextRef;
    }

    public function recheckInventory(array $input): array
    {
        $results = [];

        foreach ($input as $item) {
            $sql = "SELECT quantity FROM material_inventory WHERE material_no = :material_no AND material_description = :material_description AND model_name = :model_name LIMIT 1";
            $invParams = [
                ':material_no' => $item['material_no'],
                ':material_description' => $item['material_description'],
                ':model_name' => $item['model_name']
            ];

            $inventory = $this->db->Select($sql, $invParams);
            $results[] = [
                'material_no' => $item['material_no'],
                'material_description' => $item['material_description'],
                'model_name' => $item['model_name'],
                'quantity' => !empty($inventory) ? (int)$inventory[0]['quantity'] : null
            ];
        }
        return $results;
    }

    public function processDeliveryForm(array $input, string $lot_value, string $today, string $currentDateTime): array
    {
        $insertedCount = 0;
        $lastNumber = (int)substr($this->selectReferenceNo($today), -4);
        $inventoryList = $this->recheckInventory($input);

        foreach ($input as $index => $item) {
            $lastNumber++;
            $reference_no = $today . '-' . str_pad($lastNumber, 4, '0', STR_PAD_LEFT);

            $currentInventory = $inventoryList[$index]['quantity'] ?? 0;
            $requiredQty = (int)$item['total_quantity'];
            $newQty = $currentInventory - $requiredQty;

            if ($currentInventory < $requiredQty) {
                continue;
            }

            $insertSql = "INSERT INTO delivery_form
            (reference_no, model_name, material_no, material_description, quantity, supplement_order, total_quantity, status, section, shift, lot_no, created_at, updated_at, date_needed)
            VALUES
            (:reference_no, :model_name, :material_no, :material_description, :quantity, :supplement_order, :total_quantity, :status, :section, :shift, :lot_no, :created_at, :updated_at, :date_needed)";

            $insertParams = [
                ':reference_no' => $reference_no,
                ':model_name' => $item['model_name'] ?? '',
                ':material_no' => $item['material_no'],
                ':material_description' => $item['material_description'],
                ':quantity' => $item['quantity'] ?? 0,
                ':supplement_order' => is_numeric($item['supplement_order']) ? (int)$item['supplement_order'] : null,
                ':total_quantity' => $item['total_quantity'] ?? 0,
                ':status' => $item['status'] ?? '',
                ':section' => $item['section'] ?? '',
                ':shift' => $item['shift'] ?? '',
                ':lot_no' => $lot_value,
                ':created_at' => $currentDateTime,
                ':updated_at' => $currentDateTime,
                ':date_needed' => $item['date_needed']
            ];

            $insertResult = $this->db->Insert($insertSql, $insertParams);

            if ($insertResult !== false) {
                $insertedCount++;

                $updateSql = "UPDATE material_inventory 
                          SET quantity = :new_quantity 
                          WHERE material_no = :material_no 
                          AND material_description = :material_description 
                          AND model_name = :model_name";

                $updateParams = [
                    ':new_quantity' => $newQty,
                    ':material_no' => $item['material_no'],
                    ':material_description' => $item['material_description'],
                    ':model_name' => $item['model_name']
                ];

                $this->db->Update($updateSql, $updateParams);
            }
        }

        return [
            'status' => 'success',
            'val' => $lot_value,
            'inserted' => $insertedCount,
            'received' => count($input),
        ];
    }
}
