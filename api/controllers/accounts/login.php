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


$page_request = $_SESSION['url_request'] ?? null;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get and sanitize form data
    $user_id = trim($_POST['user_id'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($user_id) || empty($password)) {
        $_SESSION['error_message'] = "user_id and password are required.";
        header("Location: /mes/accounts/login.php");
        exit();
    }

    try {
        // Fetch user from DB
        $user = $db->SelectOne("SELECT * FROM users_new WHERE user_id = :user_id", [':user_id' => $user_id]);

        // If the user exists, verify the password using SHA-512
        if ($user && hash('sha512', $password) === $user['password']) {
    // Set session variables
    $_SESSION['id'] = $user['id'];
    $_SESSION['name'] = $user['name'];
    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['production'] = $user['production'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['production_location'] = $user['production_location'];

    $token = bin2hex(random_bytes(32));  // generate 64-char random hex string

    $_SESSION['auth_token'] = $token;
    setcookie('AuthToken', $token, [
        'expires' => time() + 86400,
        'path' => '/',
        'secure' => true,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    $role = $_SESSION['role'];
    $production = $_SESSION['production'];
    $production_location = $_SESSION['production_location'];

    $page_active = 'home'; // default

    if ($role === 'administrator' || $role === 'user manager') {
        $page_active = 'accounts';
    } elseif ($role === 'supervisor' ) {
        if ($production === 'delivery') {
            $page_active = 'submit_form';
        } elseif ($production === 'fg_warehouse') {
            $page_active = 'materials_inventory';
        } elseif ($production === 'qc') {
            $page_active = 'qc_todolist';
        }elseif ($production === 'assembly') {
            $page_active = 'assembly_todolist';
        }elseif ($production === 'stamping') {
            $page_active = 'components_inventory';
        }elseif ($production === 'rm_warehouse') {
            $page_active = 'for_issue';
        }
    } else if($role === 'line leader'){
        if ($production === 'stamping') {
            $page_active = 'stamping_todolist';
        }
    }

    $redirect = "/mes/index.php?page_active=$page_active";
    header("Location: $redirect");
    exit();
} else {
            $_SESSION['error_message'] = "Invalid user_id or password!";
            header("Location: /mes/auth/login.php");
            exit();
        }

    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Error: " . $e->getMessage();
        header("Location: /mes/auth/login.php");
        exit();
    }
}
?>
