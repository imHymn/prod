<?php
require_once __DIR__ . '/../header.php';

try {
    // SQL: Get rows where status = 'pending' and created_at within last 2 days
    $sql = "SELECT * FROM delivery_form_new 
          WHERE (status = 'pending' OR status = 'continue') AND (section = 'ASSEMBLY' OR section = 'DELIVERY')
              AND created_at >= DATE_SUB(NOW(), INTERVAL 2 DAY)";
    
    $customers = $db->Select($sql);
    echo json_encode($customers);
} catch (PDOException $e) {
    echo "DB Error: " . $e->getMessage();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
