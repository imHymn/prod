<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();

require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

require_once __DIR__ . '/../Classes/Database/DatabaseClass.php';

date_default_timezone_set('Asia/Manila');
header('Content-Type: application/json');

$db = new DatabaseClass();
$input = json_decode(file_get_contents('php://input'), true);

spl_autoload_register(function ($class) {
    $baseDir = __DIR__ . '/../Classes/';
    $file = $baseDir . str_replace('\\', '/', $class) . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
});
