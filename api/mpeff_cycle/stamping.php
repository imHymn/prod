<?php
require_once __DIR__ . '/../../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

echo json_encode([
  'OEM SMALL' => floatval($_ENV['L300_OEM_SMALL'] ?? 0),
  'BIG-HYD' => floatval($_ENV['L300_BIG_HYD'] ?? 0),
  'BIG-MECH' => floatval($_ENV['L300_BIG_MECH'] ?? 0),
  'MUFFLER COMPS' => floatval($_ENV['L300_MUFFLER_COMPS'] ?? 0),
]);
