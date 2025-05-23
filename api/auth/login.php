<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start(); // Start the session to store session data
require_once __DIR__ . '/../../database/db_connection.php';

$page_request = $_SESSION['url_request'] ?? null; // Default to null if the session variable is not set


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get and sanitize form data
    $user_id = trim($_POST['user_id']);
    $password = $_POST['password'];
    echo $user_id . $password;
   
    if (empty($user_id) || empty($password)) {
        $_SESSION['error_message'] = "user_id and password are required.";
        header("Location: login.php");
        exit();
    }

    // Prepare the SQL query to retrieve user details based on the user_id
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = :user_id");
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_STR);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // If the user exists, verify the password using SHA-512
        if ($user && hash('sha512', $password) === $user['password']) {
            // Password is correct, set session variables
            $_SESSION['id'] = $user['id'];
            $_SESSION['name']=$user['name'];
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['section'] = $user['section'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['department'] = $user['department'];
            // Redirect to the requested page or default to index2.php
            if (isset($page_request) && !empty($page_request)) {
                header("Location: $page_request");
                 exit(); // Ensure the rest of the code doesn't execute after redirection
            } else {
            header("Location: /mes/index.php");
                 exit(); // Ensure the rest of the code doesn't execute after redirection
            }
 

        } else {
            // Invalid login credentials
            $_SESSION['error_message'] = "Invalid user_id or password!";
            header("Location: /mes/auth/login.php"); // Redirect back to the login page
            exit();
        }
    } catch (PDOException $e) {
        // Handle any database connection errors
        $_SESSION['error_message'] = "Error: " . $e->getMessage();
        header("Location: /mes/auth/login.php"); // Redirect back to the login page
        exit();
    }
}
?>
