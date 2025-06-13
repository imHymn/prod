<?php
require_once __DIR__ . '/../header.php';



try {
    // SQL query to fetch customer names
    $sql = "SELECT * from fg_warehouse WHERE status='done'";
    // Use the Select method to fetch data
    $customers = $db->Select($sql);
    // Return the results as a JSON response
    echo json_encode($customers);
} catch (PDOException $e) {
    echo "DB Error: " . $e->getMessage();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
