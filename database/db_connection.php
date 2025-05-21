<?php
$host = 'localhost'; // Database host (usually localhost)
$dbname = 'robertsprod'; // Name of the database
$username = 'jepoy'; // Your database username
$password = '.@J3p0y1993'; // Your database password (default is empty for XAMPP)

try {
    // Create a PDO instance
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    // Set the PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Handle connection error
    die("Connection failed: " . $e->getMessage());
}
?>
