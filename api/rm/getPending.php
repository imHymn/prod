<?php
// Show errors (for debugging)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include Composer autoloader with correct relative path
require_once __DIR__ . '/../../vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../env');
$dotenv->load();
require_once __DIR__ . '/../../Classes/Database/DatabaseClass.php'; // Adjust the path to your class file
$db = new DatabaseClass();

try {
    // SQL query to fetch customer names
    $sql = "SELECT * FROM `pending_rmwarehouse` WHERE status='pending' AND section ='rm'";
    // Use the Select method to fetch data
    $users = $db->Select($sql);
    // Return the results as a JSON response
    echo json_encode($users);
} catch (PDOException $e) {
    echo "DB Error: " . $e->getMessage();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
