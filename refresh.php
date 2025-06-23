<?php
// Show errors (for debugging)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include Composer autoloader with correct relative path
require_once __DIR__ . '/vendor/autoload.php';


// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

require_once __DIR__ . '/Classes/Database/DatabaseClass.php'; // Adjust the path to your class file
$db = new DatabaseClass();

try {
    $sql1 = "UPDATE components_inventory SET actual_inventory = 500, rm_stocks=0";
    $db->Update($sql1);

    $sql2 = "UPDATE material_inventory SET quantity = 100";
    $db->Update($sql2);

    $tablesToTruncate = [
        'fg_warehouse',
        'rm_warehouse',
        'stamping',
        'delivery_form',
        'assembly_list',
        'qc_list',
        'rework_assembly',
        'rework_qc',
        'issued_rawmaterials'
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
