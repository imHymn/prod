<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/vendor/autoload.php';

// Load environment variables from .env
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();


require_once __DIR__ . '/Classes/Database/DatabaseClass.php';

// Path to SQL file
$sqlFilePath = __DIR__ . '/sql/robertsprod.sql';

if (!file_exists($sqlFilePath)) {
    exit(json_encode([
        'success' => false,
        'message' => "❌ SQL file not found at: $sqlFilePath"
    ]));
}

try {
    // Step 1: Connect to MySQL without selecting DB (to drop & recreate safely)
    $host = $_ENV['DB_HOST'];
    $user = $_ENV['DB_USER'];
    $pass = $_ENV['DB_PASS'];
    $dbname = $_ENV['DB_NAME'];

    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Step 2: Drop and recreate the database (⚠️ this wipes existing data)
    $pdo->exec("DROP DATABASE IF EXISTS `$dbname`");
    $pdo->exec("CREATE DATABASE `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `$dbname`");

    // Step 3: Load SQL content
    $sql = file_get_contents($sqlFilePath);

    // Optional: split & execute line-by-line (for large files or safer error tracking)
    $pdo->setAttribute(PDO::MYSQL_ATTR_MULTI_STATEMENTS, true);
    $pdo->exec($sql);

    echo json_encode([
        'success' => true,
        'message' => "✅ Database '$dbname' successfully imported from robertsprod.sql"
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => "❌ Import failed: " . $e->getMessage()
    ]);
}
