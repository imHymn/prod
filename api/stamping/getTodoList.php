<?php
require_once __DIR__ . '/../header.php';



try {
    $section = $_GET['section'] ?? null;

    if (!$section) {
        echo json_encode(['error' => 'Missing section']);
        exit;
    }

    if ($section === "all") {
        $sql = "SELECT * FROM stamping WHERE created_at >= DATE_SUB(NOW(), INTERVAL 2 DAY)";
        $data = $db->Select($sql);
    } else {
            $normalizedSection = str_replace('-', ' ', $section);  // Normalize input
            $sql = "SELECT * FROM stamping 
                    WHERE REPLACE(section, '-', ' ') = :section 
                    AND created_at >= DATE_SUB(NOW(), INTERVAL 2 DAY)";
            $data = $db->Select($sql, [':section' => $normalizedSection]);

    }

    echo json_encode($data);

} catch (PDOException $e) {
    echo json_encode(['error' => 'DB Error: ' . $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['error' => 'Error: ' . $e->getMessage()]);
}
