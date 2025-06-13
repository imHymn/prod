<?php
require_once __DIR__ . '/../header.php';


try {
    // SQL query to fetch customer names
    $sql = "SELECT *
            FROM components_inventory
            WHERE actual_inventory < normal
           ;
            ";
    // Use the Select method to fetch data
    $users = $db->Select($sql);
    // Return the results as a JSON response
    echo json_encode($users);
} catch (PDOException $e) {
    echo "DB Error: " . $e->getMessage();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
