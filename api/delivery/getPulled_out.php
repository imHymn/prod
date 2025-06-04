<?php
// Show errors (for debugging)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include Composer autoloader
require_once __DIR__ . '/../../vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../env');
$dotenv->load();

require_once __DIR__ . '/../../Classes/Database/DatabaseClass.php';
$db = new DatabaseClass();

try {
    // SQL: Get rows where status = 'pending' and created_at within last 2 days
    $sql = "SELECT * FROM delivery_form_new 
          WHERE (status = 'pending' OR status = 'continue') AND (section = 'ASSEMBLY' OR section = 'DELIVERY')
              AND created_at >= DATE_SUB(NOW(), INTERVAL 2 DAY)";
    
    $customers = $db->Select($sql);
    echo json_encode($customers);
} catch (PDOException $e) {
    echo "DB Error: " . $e->getMessage();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
