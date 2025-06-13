<?php
require_once __DIR__ . '/../header.php';


use Model\AccountModel;
use Validation\AccountValidator;

$account = new AccountModel($db);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and collect POST data
    $user_id = trim($_POST['user_id'] ?? '');
    $password = $_POST['password'] ?? '';

    // Validate login inputs
    $validationErrors = AccountValidator::validateLogin(['user_id' => $user_id, 'password' => $password]);

    if (!empty($validationErrors)) {
        $_SESSION['error_message'] = implode(' ', $validationErrors);
        header("Location: /mes/auth/login.php");
        exit();
    }
    try {
        $user = $account->getUserByUserId($user_id);

        if ($user && hash('sha512', $password) === $user['password']) {
            // Set session values
            $_SESSION['id'] = $user['id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['production'] = $user['production'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['production_location'] = $user['production_location'];

            // Generate and set AuthToken
            $token = bin2hex(random_bytes(32));
            $_SESSION['auth_token'] = $token;

            setcookie('AuthToken', $token, [
                'expires' => time() + 86400,
                'path' => '/',
                'secure' => true,
                'httponly' => true,
                'samesite' => 'Lax',
            ]);

            // Page redirection logic
            $page_active = 'home';
            $role = $user['role'];
            $production = $user['production'];

            if ($role === 'administrator' || $role === 'user manager') {
                $page_active = 'accounts';
            } elseif ($role === 'supervisor') {
                $page_active = match ($production) {
                    'delivery' => 'submit_form',
                    'fg_warehouse' => 'materials_inventory',
                    'qc' => 'qc_todolist',
                    'assembly' => 'assembly_todolist',
                    'stamping' => 'components_inventory',
                    'rm_warehouse' => 'for_issue',
                    default => 'home'
                };
            } elseif ($role === 'line leader' && $production === 'stamping') {
                $page_active = 'stamping_todolist';
            }

            header("Location: /mes/index.php?page_active=$page_active");
            exit();
        } else {
            $_SESSION['error_message'] = "Invalid user_id or password!";
            header("Location: /mes/auth/login.php");
            exit();
        }
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Database error: " . $e->getMessage();
        header("Location: /mes/auth/login.php");
        exit();
    }
}
