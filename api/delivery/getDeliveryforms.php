<?php
require_once __DIR__ . '/../header.php';




try {
    $sql = "SELECT * FROM delivery_form_new
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 2 DAY)";

    // section ='DELIVERY' AND  status ='pending' AND 
    $customers = $db->Select($sql);
    echo json_encode($customers);
} catch (PDOException $e) {
    echo "DB Error: " . $e->getMessage();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
