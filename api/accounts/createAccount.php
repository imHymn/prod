<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../database/db_connection.php'; 

// Set response type
header('Content-Type: application/json');

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

$user_id = trim($input['user_id'] ?? '');
$email = trim($input['email'] ?? '');
$password = $input['password'] ?? '';
$section = trim($input['section'] ?? '');
$department = trim($input['department'] ?? '');

// Basic validation
if (empty($user_id) || empty($email) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'All required fields are missing.']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = :user_id OR email = :email");
    $stmt->execute([':user_id' => $user_id, ':email' => $email]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => false, 'message' => 'user_id or email already in use.']);
        exit;
    }

    $hashed_password = hash('sha512', $password);

    $stmt = $pdo->prepare("
        INSERT INTO users (user_id, email, password, section, department)
        VALUES (:user_id, :email, :password, :section, :department)
    ");
    $stmt->execute([
        ':user_id' => $user_id,
        ':email' => $email,
        ':password' => $hashed_password,
        ':section' => $section,
        ':department' => $department
    ]);

    echo json_encode(['success' => true, 'message' => 'Account created successfully.']);
    exit;

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    exit;
}
?>
