<?php
session_start();
require_once __DIR__ . '/../../../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../../');
$dotenv->load();

require_once __DIR__ . '/../../../Classes/Database/DatabaseClass.php';
$db = new DatabaseClass();
date_default_timezone_set('Asia/Manila');

header('Content-Type: application/json');
$input = json_decode(file_get_contents('php://input'), true);


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
