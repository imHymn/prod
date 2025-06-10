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


header('Content-Type: application/json');

try {
    // Fetch users with role supervisor or administrator
    $sql = "SELECT * FROM users_new";
    $users = $db->Select($sql);
    echo json_encode($users);
} catch (PDOException $e) {
    echo json_encode(['error' => 'DB Error', 'message' => $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['error' => 'Error', 'message' => $e->getMessage()]);
}
