<?php
session_start();
$current_email = $_SESSION['email'];
if($_SERVER['REQUEST_METHOD']==='POST'){
    $newEmail = filter_input(INPUT_POST, 'new_email', FILTER_VALIDATE_EMAIL);

    echo $current_email . $newEmail;
}
?>