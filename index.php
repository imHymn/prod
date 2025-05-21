<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");
date_default_timezone_set('Asia/Manila');
include 'components/session.php';
include 'components/header.php';


if (isset($_GET['page_active'])) {
    $page = $_GET['page_active'] . '.php';
    $paths = [
        '',
        'dtr/',
        'Stamping/',
        'mes/',
        'auth/',
        'yet_another_subdirectory/'
    ];

    $found = false;
    foreach ($paths as $path) {
        if (file_exists($path . $page)) {
            include $path . $page;
            $found = true;
            break;
        }
    }

    if (!$found) {
        include 'error.php'; // Or include 'home.php' for the default page
    }
} else {
    include 'error.php'; // Or include 'home.php' for the default page
}




include 'components/footer.php';



?>