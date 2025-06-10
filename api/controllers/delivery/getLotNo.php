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
