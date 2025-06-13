<?php
require_once __DIR__ . '/../header.php';


try {
    $sql = "SELECT * FROM components_task";
    $result = $db->Select($sql);  // No parameters needed

    echo json_encode($result);

} catch (PDOException $e) {
    echo json_encode(['error' => 'DB Error: ' . $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['error' => 'Error: ' . $e->getMessage()]);
}
