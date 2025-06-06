<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");
date_default_timezone_set('Asia/Manila');

include 'components/session.php';
include 'components/header.php';

define('MES_ACCESS', true);

// ✅ Grouped page map: folder => [allowed page_active => filename]
$pageMap = [
    'admin' => [
        'accounts' => 'accounts.php',
    ],
    'assembly' => [
        'assembly_todolist' => 'todo_list.php',
        'assembly_worklogs' => 'work_logs.php',
        'assembly_manpower_efficiency'=>'manpower_efficiency.php',
        'assembly_rework'    => 'rework.php',
    ],
    'delivery' => [
        'submit_form' => 'submit_form.php',
        'pulled_out'  => 'pulled_out.php',
    
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
        'stamping_oem_small' => 'section/oem_small.php',
        'stamping_muffler_comps' => 'section/muffler_comps.php',
        'stamping_big_hyd' => 'section/big_hyd.php',
        'stamping_big_mech' => 'section/big_mech.php',


 
    ],
    'warehouse' => [
        'materials_inventory' => 'materials_inventory.php',
        'for_pulling'    => 'for_pulling.php',
        'pulling_history'    => 'pulling_history.php',
    ],
];

if (isset($_GET['page_active'])) {
    $requestedPage = basename($_GET['page_active']); // sanitize input

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
?>
