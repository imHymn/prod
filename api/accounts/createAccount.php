<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

// Load Composer & env
require_once __DIR__ . '/../../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../env');
$dotenv->load();

// Use custom database class
require_once __DIR__ . '/../../Classes/Database/DatabaseClass.php';
$db = new DatabaseClass();

date_default_timezone_set('Asia/Manila');

// Get POST input
$input = json_decode(file_get_contents('php://input'), true);

// Sanitize input
function trimOrNull($value) {
    $trimmed = trim($value ?? '');
    return $trimmed === '' ? null : $trimmed;
}

$name = trimOrNull($input['name'] ?? null);
$user_id = trimOrNull($input['user_id'] ?? null);
$production = trimOrNull($input['production'] ?? null);
$role = trimOrNull($input['role'] ?? null);
$production_location = trimOrNull($input['production_location'] ?? null);
$password = $input['password'] ?? null;

$created_at = date('Y-m-d H:i:s');

// Basic validation
if (empty($user_id) || empty($password) || empty($name)) {
    echo json_encode(['success' => false, 'message' => 'Required fields are missing.']);
    exit;
}

try {
    // Check for existing user_id
    $existing = $db->SelectOne("SELECT * FROM users_new WHERE user_id = :user_id", [
        ':user_id' => $user_id
    ]);

    if ($existing) {
        echo json_encode(['success' => false, 'message' => 'User ID already in use.']);
        exit;
    }

    // Hash password
    $hashed_password = hash('sha512', $password);

    // Insert new user
    $inserted = $db->Insert("
        INSERT INTO users_new (name, user_id, password, production, role, production_location, created_at)
        VALUES (:name, :user_id, :password, :production, :role, :production_location, :created_at)
    ", [
        ':name' => $name,
        ':user_id' => $user_id,
        ':password' => $hashed_password,
        ':production' => $production,
        ':role' => $role,
        ':production_location' => $production_location,
        ':created_at' => $created_at
    ]);

    if ($inserted) {
        echo json_encode(['success' => true, 'message' => 'Account created successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Insert failed.']);
    }

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
