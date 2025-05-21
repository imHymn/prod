<?php
// Ensure that the customer is provided via the GET request
if (isset($_GET['customer'])) {
    $customerName = $_GET['customer'];
    $modelName = $_GET['model'];
    
    // Autoload and other necessary configurations
    require_once __DIR__ . '/../../vendor/autoload.php';
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../env');
    $dotenv->load();
    
    // Database class instantiation
    require_once __DIR__ . '/../../Classes/Database/DatabaseClass.php';
    $db = new DatabaseClass();
    $tableHtml = '';
    try {
        // SQL query to fetch SKU based on the customer name and model name
        $sql = "SELECT material_description, material_no FROM material_inventory WHERE customer_name = :customer_name AND model_name = :model_name";
        
        // Fetch the models for the selected customer and model name
        $sku = $db->Select($sql, ['customer_name' => $customerName ,'model_name' => $modelName]);
       $tableHtml.="<table class='table table'>
                           ";
        if ($sku) {
            $counter = 0;
        foreach ($sku as $row) {
           $materialNumber =   $row['material_no'];
                $materialDescription =   $row['material_description'];
          $tableHtml.=" 

          <tr><td>Material No.</td>
          <td>Material Description </td>
          <td>Supplement Order</td></tr>
          <tr>
          <td>$materialNumber</td>
          <td>$materialDescription</td> 
      <td>
    <div class='d-flex align-items-center'>
        <input class='form-control mb-2' id='supplement$counter' />
        <label class='totalQty'></label>
    </div>
</td>

                                </tr>";
                                $counter++;
                         

        }
    $tableHtml .=    "</table>";
            echo json_encode(['tableHtml' => $tableHtml]);
        } else {
            echo json_encode(['error' => 'No data found']);
        }
    } catch (PDOException $e) {
        // Handle database error
        echo json_encode(['error' => 'DB Error: ' . $e->getMessage()]);
    } catch (Exception $e) {
        // Handle general error
        echo json_encode(['error' => 'Error: ' . $e->getMessage()]);
    }
} else {
    // Missing customer parameter
    echo json_encode(['error' => 'Customer parameter missing']);
}
?>
