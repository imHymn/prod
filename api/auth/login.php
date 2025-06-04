<?php
// Show errors (for debugging)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start session
session_start();

// Include Composer autoloader
require_once __DIR__ . '/../../vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../env');
$dotenv->load();

// Include database class
require_once __DIR__ . '/../../Classes/Database/DatabaseClass.php';
$db = new DatabaseClass();

$page_request = $_SESSION['url_request'] ?? null;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get and sanitize form data
    $user_id = trim($_POST['user_id'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($user_id) || empty($password)) {
        $_SESSION['error_message'] = "user_id and password are required.";
        header("Location: /mes/auth/login.php");
        exit();
    }

    try {
        // Fetch user from DB
        $user = $db->SelectOne("SELECT * FROM users WHERE user_id = :user_id", [':user_id' => $user_id]);

        // If the user exists, verify the password using SHA-512
        if ($user && hash('sha512', $password) === $user['password']) {
            // Set session variables
            $_SESSION['id'] = $user['id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['production'] = $user['production'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['section'] = $user['section'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['department'] = $user['department'];

            // Redirect to requested page or default
            $redirect = !empty($page_request) ? $page_request : '/mes/index.php';
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
