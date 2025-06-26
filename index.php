<?php
session_start();
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");
date_default_timezone_set('Asia/Manila');

if (!isset($_COOKIE['AuthToken'], $_SESSION['auth_token']) || $_COOKIE['AuthToken'] !== $_SESSION['auth_token']) {
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }
    session_destroy();
    setcookie('AuthToken', '', time() - 3600, '/');
    header('Location: /mes/auth/login.php');
    exit();
}

// if (!isset($_SESSION['role'])) {

//     header('Location: /mes/auth/login.php');
//     exit();
// }

// if ($_SESSION['role'] === 'worker') {

//     $_SESSION = [];
//     session_destroy();
//     setcookie('AuthToken', '', time() - 3600, '/');

//     header('Location: /mes/auth/login.php');
//     exit();
// }


include 'components/session.php';
include 'components/header.php';

define('MES_ACCESS', true);

// ✅ Grouped page map: folder => [allowed page_active => filename]
$pageMap = [
    'admin' => [
        'accounts' => 'accounts.php',
        'user' => 'user.php',
    ],
    'assembly' => [
        'assembly_todolist_sku' => 'todo_list_sku.php',
        'assembly_todolist_component' => 'todo_list_component.php',
        'assembly_worklogs' => 'work_logs.php',
        'assembly_manpower_efficiency' => 'manpower_efficiency.php',
        'assembly_rework'    => 'rework.php',
    ],
    'delivery' => [
        'submit_form' => 'submit_form.php',
        'pulled_out'  => 'pulled_out.php',
        'pulled_history'  => 'pulled_history.php',

    ],
    'qc' => [
        'qc_todolist' => 'todo_list.php',
        'qc_worklogs' => 'work_logs.php',
        'qc_rework'    => 'rework.php',
        'qc_manpower_efficiency'    => 'manpower_efficiency.php',
    ],
    'rm' => [
        'for_issue' => 'for_issue.php',
        'issued_history' => 'issued_history.php',
    ],
    'stamping' => [
        'stamping_todolist'   => 'todo_list.php',
        'components_inventory' => 'components_inventory.php',
        'stamping_monitoring_data' => 'monitoring_data.php',
        'stamping_work_logs' => 'work_logs.php',
    ],
    'warehouse' => [
        'materials_inventory' => 'materials_inventory.php',
        'for_pulling'    => 'for_pulling.php',
        'pulling_history'    => 'pulling_history.php',
    ],
];

if (isset($_GET['page_active'])) {
    $requestedPage = basename($_GET['page_active']); // sanitize input
    $GLOBALS['page_active'] = $requestedPage;
    $_SESSION['page_active'] = $requestedPage;

    $found = false;

    // ✅ Loop through folders and see if requestedPage exists in any
    foreach ($pageMap as $folder => $pages) {
        if (array_key_exists($requestedPage, $pages)) {
            $file = "pages/{$folder}/{$pages[$requestedPage]}";
            if (file_exists($file)) {
                include $file;
                $found = true;
                break;
            }
        }
    }

    if (!$found) {
        include 'error.php';
    }
} else {
    include 'error.php';
}

include 'components/footer.php';
