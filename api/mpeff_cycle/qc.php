<?php
require_once __DIR__ . '/../../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

// require_once __DIR__ . '/../header.php';

echo json_encode([
  'L300' => floatval($_ENV['L300_ASSY'] ?? 0),
]);
