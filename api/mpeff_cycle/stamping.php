<?php
require_once __DIR__ . '/../header.php';

try {
  $sql = "SELECT material_no, stage_name FROM components_inventory";
  $data = $db->Select($sql);
  echo json_encode($data);
} catch (PDOException $e) {
  http_response_code(500);
  echo json_encode(["error" => "DB Error: " . $e->getMessage()]);
} catch (Exception $e) {
  http_response_code(500);
  echo json_encode(["error" => "Error: " . $e->getMessage()]);
}
