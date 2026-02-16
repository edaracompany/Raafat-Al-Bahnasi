<?php
header('Content-Type: application/json');
require_once '../config/database.php';

$stmt = $pdo->query("SELECT * FROM brands ORDER BY name_ar");
$brands = $stmt->fetchAll();

echo json_encode([
    'success' => true,
    'brands' => $brands
]);
?>