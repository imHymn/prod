<?php
require_once __DIR__ . '/../header.php';



try {
    // SQL query to fetch customer names
    $sql = "SELECT * FROM `assembly_list` WHERE status_qc ='done'";
    // Use the Select method to fetch data
    $users = $db->Select($sql);
    // Return the results as a JSON response
    echo json_encode($users);
} catch (PDOException $e) {
    echo "DB Error: " . $e->getMessage();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
