<?php
require_once __DIR__ . '/../header.php';




if (isset($_GET['customer'])) {
    $customerName = $_GET['customer'];
    $modelName = $_GET['model'];
    
    $tableHtml = '';
    try {
        // SQL query to fetch SKU based on the customer name and model name
        $sql = "SELECT material_description, material_no FROM material_inventory WHERE customer_name = :customer_name AND model_name = :model_name";
        
        // Fetch the models for the selected customer and model name
        $sku = $db->Select($sql, ['customer_name' => $customerName ,'model_name' => $modelName]);
  $tableHtml = "<table class='table table'>
    <thead>
        <tr>
            <th>Material No.</th>
            <th>Material Description</th>
            <th>Supplement Order</th>
            <th class='text-center'>Total Quantity</th>
        </tr>
    </thead>
    <tbody>";
        if ($sku) {
            $counter = 0;
        foreach ($sku as $row) {
           $materialNumber =   $row['material_no'];
                $materialDescription =   $row['material_description'];
          $tableHtml.=" 

        
          <tr>
      <td class='materialNo'>$materialNumber</td>
            <td class='materialDesc'>$materialDescription</td>
      <td>
    <div class='d-flex '>
        <input class='form-control mb-2' id='supplement$counter' />
       
    </div>
</td>
<td class='d-flex justify-content-center align-items-center'>
  <label class='totalQty'></label>
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
