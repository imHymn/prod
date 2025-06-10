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
    $sql = "SELECT * FROM components_task";
    $result = $db->Select($sql);  // No parameters needed

    echo json_encode($result);

} catch (PDOException $e) {
    echo json_encode(['error' => 'DB Error: ' . $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['error' => 'Error: ' . $e->getMessage()]);
}
