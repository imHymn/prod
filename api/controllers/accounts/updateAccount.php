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


date_default_timezone_set('Asia/Manila');

$input = json_decode(file_get_contents('php://input'), true);

function trimOrNull($value) {
    $trimmed = trim($value ?? '');
    return $trimmed === '' ? null : $trimmed;
}

// Use id (primary key) to identify user record
$id = trimOrNull($input['id'] ?? null);

if (empty($id)) {
    echo json_encode(['success' => false, 'message' => 'ID is required.']);
    exit;
}

// Optional update fields
$name = array_key_exists('name', $input) ? trimOrNull($input['name']) : null;
$production = array_key_exists('production', $input) ? trimOrNull($input['production']) : null;
$role = array_key_exists('role', $input) ? trimOrNull($input['role']) : null;
$production_location = array_key_exists('production_location', $input) ? trimOrNull($input['production_location']) : null;
$password = array_key_exists('password', $input) ? $input['password'] : null;

// Build update set dynamically
$fields = [];
$params = [];

if ($name !== null) {
    $fields[] = 'name = :name';
    $params[':name'] = $name;
}
if ($production !== null) {
    $fields[] = 'production = :production';
    $params[':production'] = $production;
}
if ($role !== null) {
    $fields[] = 'role = :role';
    $params[':role'] = $role;
}
if ($production_location !== null) {
    $fields[] = 'production_location = :production_location';
    $params[':production_location'] = $production_location;
}
if (!empty($password)) {
    // Hash password
    $hashed_password = hash('sha512', $password);
    $fields[] = 'password = :password';
    $params[':password'] = $hashed_password;
}

if (empty($fields)) {
    echo json_encode(['success' => false, 'message' => 'No fields to update.']);
    exit;
}

$params[':id'] = $id;

try {
    // Check if user exists by id
    $existing = $db->SelectOne("SELECT * FROM users_new WHERE id = :id", [
        ':id' => $id
    ]);

    if (!$existing) {
        echo json_encode(['success' => false, 'message' => 'ID does not exist.']);
        exit;
    }

    $sql = "UPDATE users_new SET " . implode(', ', $fields) . " WHERE id = :id";

    $updated = $db->Update($sql, $params);

    if ($updated) {
        echo json_encode(['success' => true, 'message' => 'Account updated successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'No changes made or update failed.']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
