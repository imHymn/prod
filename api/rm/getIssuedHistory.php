<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../env');
$dotenv->load();

require_once __DIR__ . '/../../Classes/Database/DatabaseClass.php';
$db = new DatabaseClass();

try {
    $sql = "SELECT * FROM rm_warehouse";

    $results = $db->Select($sql);

    echo json_encode([
        'status' => 'success',
        'data' => $results
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'DB Error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
