<?php
require_once __DIR__ . '/../header.php';


if (isset($_GET['customer'])) {
    $customerName = $_GET['customer'];
    try {
        // SQL query to fetch models based on the customer name
        $sql = "SELECT DISTINCT model_name FROM material_inventory WHERE customer_name = :customer_name";
        // Fetch the models for the selected customer
        $models = $db->Select($sql, ['customer_name' => $customerName]);

        // Return the models as a JSON response
        echo json_encode($models);
    } catch (PDOException $e) {
        echo json_encode(['error' => 'DB Error: ' . $e->getMessage()]);
    } catch (Exception $e) {
        echo json_encode(['error' => 'Error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['error' => 'Customer parameter missing']);
}
?>
