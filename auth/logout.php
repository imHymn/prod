<?php
session_start();

unset($_SESSION['id']);

header("Location: /mes/auth/login.php");
exit();
?>
