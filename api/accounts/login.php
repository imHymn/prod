<?php
require_once __DIR__ . '/../header.php';

use Model\AccountModel;
use Validation\AccountValidator;



$account = new AccountModel($db);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

/* ---------- 1. Validate input ---------- */
$user_id  = trim($_POST['user_id'] ?? '');
$password = $_POST['password'] ?? '';

$errors = AccountValidator::validateLogin(['user_id' => $user_id, 'password' => $password]);
if ($errors) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => implode(' ', $errors)]);
    exit();
}

try {
    /* ---------- credential check ---------- */
    $user = $account->getUserByUserId($user_id);

    if (!$user || hash('sha512', $password) !== $user['password']) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Invalid user ID or password']);
        exit();
    }

    /* ---------- decide landing page first ---------- */
    $page_active  = null;
    $role         = $user['role'];
    $production   = $user['production'];

    if ($role === 'administrator' || $role === 'user manager') {
        $page_active = 'accounts';
    } elseif ($role === 'supervisor') {
        $page_active = match ($production) {
            'delivery'       => 'submit_form',
            'fg_warehouse'   => 'materials_inventory',
            'qc'             => 'qc_todolist',
            'assembly'       => 'assembly_todolist',
            'stamping'       => 'components_inventory',
            'rm_warehouse'   => 'for_issue',
            default          => null,
        };
    } elseif ($role === 'line leader' && $production === 'stamping') {
        $page_active = 'stamping_todolist';
    }

    /* ---------- abort early if role/production not allowed ---------- */
    if ($page_active === null) {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'message' => 'You don’t have any access.'
        ]);
        exit();
    }

    /* ---------- now it’s safe to open the session & mint the token ---------- */
    $_SESSION['id']                 = $user['id'];
    $_SESSION['name']               = $user['name'];
    $_SESSION['user_id']            = $user['user_id'];
    $_SESSION['production']         = $production;
    $_SESSION['role']               = $role;
    $_SESSION['production_location'] = $user['production_location'];

    $token = bin2hex(random_bytes(32));
    $_SESSION['auth_token'] = $token;

    setcookie('AuthToken', $token, [
        'expires'  => time() + 86400,
        'path'     => '/',
        'secure'   => true,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    echo json_encode([
        'success'     => true,
        'page_active' => $page_active,
        'name'        => $user['name'],
    ]);
    exit();
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    exit();
}
