<?php
// Show errors (for debugging)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include Composer autoloader
require_once __DIR__ . '/../../vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../env');
$dotenv->load();

// Include database class
require_once __DIR__ . '/../../Classes/Database/DatabaseClass.php';
$db = new DatabaseClass();

   $user_id = trim($_POST['user_id']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $section='ADMINISTRATOR';

    echo $user_id . $user_id . $email . $password . $section;
    // Basic validation
    if (empty($user_id) || empty($email) || empty($password)) {
        $_SESSION['error_message'] = "All fields are required.";
        header("Location: ../auth/register.php");
        exit();
    }

    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = :user_id OR email = :email");
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_STR);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $_SESSION['error_message'] = "user_id or email already in use.";
            header("Location: ../auth/register.php");
            exit();
        }

        // Hash the password
        $hashed_password = hash('sha512',$password);

        // Insert new user into the database
        $stmt = $pdo->prepare("INSERT INTO users (user_id, email, password,section) VALUES (:user_id, :email, :password,:section )");
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_STR);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->bindParam(':section', $section, PDO::PARAM_STR);
        $stmt->bindParam(':password', $hashed_password, PDO::PARAM_STR);

        if ($stmt->execute()) {
            $_SESSION['register_success'] = "Registration successful! You can now log in.";
            header("Location: /mes/auth/login.php"); // Redirect to login page
            exit();
        } else {
            $_SESSION['register_error'] = "Something went wrong. Please try again.";
            header("Location: ../auth/register.php");
            exit();
        }
    } catch (PDOException $e) {
        $_SESSION['reguster_error'] = "Error: " . $e->getMessage();
        header("Location: ../auth/register.php");
        exit();
    }

?>
