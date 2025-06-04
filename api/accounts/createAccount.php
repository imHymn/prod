<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../database/db_connection.php'; 
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);

$name = trim($input['name'] ?? '');
$user_id = trim($input['user_id'] ?? '');
$password = $input['password'] ?? '';
$production = trim($input['section'] ?? '');
$role = trim($input['role'] ?? '');

// Basic validation
if (empty($user_id) || empty($password) || empty($name)) {
    echo json_encode(['success' => false, 'message' => 'Required fields are missing.']);
    exit;
}

try {
    // Check if user_id already exists
    $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = :user_id");
    $stmt->execute([':user_id' => $user_id]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => false, 'message' => 'User ID already in use.']);
        exit;
    }

    $hashed_password = hash('sha512', $password);

    // Insert user
    $stmt = $pdo->prepare("
        INSERT INTO users (name, user_id, password, production, role)
        VALUES (:name, :user_id, :password, :production, :role)
    ");
    $stmt->execute([
        ':name' => $name,
        ':user_id' => $user_id,
        ':password' => $hashed_password,
        ':production' => $production,
        ':role' => $role
    ]);

    echo json_encode(['success' => true, 'message' => 'Account created successfully.']);
    exit;

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    exit;
}
?>
