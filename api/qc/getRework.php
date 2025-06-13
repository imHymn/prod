<?php
require_once __DIR__ . '/../header.php';



try {
    $sql = "SELECT * FROM rework_qc
            WHERE section = 'qc'
              AND status IN ('pending','continue')
              AND created_at >= DATE_SUB(NOW(), INTERVAL 2 DAY)";

    
    $customers = $db->Select($sql);
    echo json_encode($customers);
} catch (PDOException $e) {
    echo "DB Error: " . $e->getMessage();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
