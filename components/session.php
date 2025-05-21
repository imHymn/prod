<?php
session_start();
$request_uri = $_SERVER['REQUEST_URI'];
// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['url_request'] = $request_uri;
    header("Location:  /mes/auth/login.php "); // Redirect to login page if not logged in
    exit();
}


?>