<?php
require_once __DIR__ . '/../header.php';




try {

    $model_name = $_GET['model_name'];
    // SQL query to fetch customer names
    $sql = "SELECT lot_no FROM delivery_forms WHERE model_name = :model_name ORDER BY lot_no DESC LIMIT 1";
   
    $lot_no = $db->Select($sql, [':model_name' => $model_name]);
    // Return the results as a JSON response
    echo json_encode($lot_no);
} catch (PDOException $e) {
    echo "DB Error: " . $e->getMessage();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
