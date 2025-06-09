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
