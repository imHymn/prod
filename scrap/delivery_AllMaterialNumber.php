<?php
// Ensure that the customer is provided via the GET request
if (isset($_GET['customer'], $_GET['model'])) {
    $customerName = $_GET['customer'];
    $modelName = $_GET['model'];
    require_once __DIR__ . '/../../vendor/autoload.php';
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/env');
    $dotenv->load();
    require_once __DIR__ . '/../../Classes/Database/DatabaseClass.php';
    $db = new DatabaseClass();


    try {
        // SQL query to fetch models based on the customer name
        $sql = "SELECT material_no ,material_description  FROM material_inventory WHERE customer_name = :customer_name AND model_name =:model_name";
        // Fetch the models for the selected customer
        $materialNumber = $db->Select($sql, ['customer_name' => $customerName ,'model_name'=>$modelName]);

        // Return the models as a JSON response
        echo json_encode($materialNumber);
    } catch (PDOException $e) {
        echo json_encode(['error' => 'DB Error: ' . $e->getMessage()]);
    } catch (Exception $e) {
        echo json_encode(['error' => 'Error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['error' => 'Customer parameter missing']);
}
?>
