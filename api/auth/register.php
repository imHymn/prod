<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start(); // Start the session
require_once __DIR__ . '/../../database/db_connection.php'; // Include database connection


    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $section='ADMINISTRATOR';
    // Basic validation
    if (empty($username) || empty($email) || empty($password)) {
        $_SESSION['error_message'] = "All fields are required.";
        header("Location: ../auth/register.php");
        exit();
    }

    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username OR email = :email");
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $_SESSION['error_message'] = "Username or email already in use.";
            header("Location: ../auth/register.php");
            exit();
        }

        // Hash the password
        $hashed_password = hash('sha512',$password);

        // Insert new user into the database
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password,section) VALUES (:username, :email, :password,:section )");
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
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
