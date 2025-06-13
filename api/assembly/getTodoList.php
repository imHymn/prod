<?php
require_once __DIR__ . '/../header.php';



try {
    // SQL: Get rows where status = 'pending' and created_at within last 2 days
    $sql = "SELECT * FROM assembly_list_new 
            WHERE  created_at >= DATE_SUB(NOW(), INTERVAL 2 DAY)";
    
    $customers = $db->Select($sql);
    echo json_encode($customers);
} catch (PDOException $e) {
    echo "DB Error: " . $e->getMessage();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
