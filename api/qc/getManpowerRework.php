<?php
require_once __DIR__ . '/../header.php';



try {
    $sql = "SELECT * FROM rework_qc
            WHERE qc_timeout IS NOT NULL";

    
    $customers = $db->Select($sql);
    echo json_encode($customers);
} catch (PDOException $e) {
    echo "DB Error: " . $e->getMessage();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
