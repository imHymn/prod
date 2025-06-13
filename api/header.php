<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();

require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

require_once __DIR__ . '/../Classes/Database/DatabaseClass.php';
require_once __DIR__ . '/../Classes/Model/accounts.php';
require_once __DIR__ . '/../Classes/Validation/accounts.php';

date_default_timezone_set('Asia/Manila');
header('Content-Type: application/json');

$db = new DatabaseClass();
$input = json_decode(file_get_contents('php://input'), true);
