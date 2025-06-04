<?php
// Show errors (for debugging)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include Composer autoloader with correct relative path
require_once __DIR__ . '/vendor/autoload.php';


// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/env');
$dotenv->load();

require_once __DIR__ . '/Classes/Database/DatabaseClass.php'; // Adjust the path to your class file
$db = new DatabaseClass();

try {
    // Update components_inventory actual_inventory to 500
    $sql1 = "UPDATE components_inventory SET actual_inventory = 500, rm_stocks=0";
    $db->Update($sql1);

    // Update material_inventory quantity to 100
    $sql2 = "UPDATE material_inventory SET quantity = 100";
    $db->Update($sql2);


    // Truncate tables one by one
    $tablesToTruncate = [
        'assembly_list',
        'components_task',
        'delivery_forms',
        'fg_warehouse',
        'stock_warehouse',
        'pending_rmwarehouse',
        'stamping',
        'delivery_form_new',
        'assembly_list_new',
        'qc_list',
        'rework_assembly',
         'rework_qc'
    ];
    foreach ($tablesToTruncate as $table) {
        $db->Update("TRUNCATE TABLE `$table`");
    }

    echo json_encode(['success' => true, 'message' => 'Tables updated and truncated successfully.']);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'DB Error: ' . $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

