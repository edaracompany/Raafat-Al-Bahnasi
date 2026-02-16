<?php
header('Content-Type: application/json');
require_once '../config/database.php';

$stmt = $pdo->query("SELECT * FROM categories ORDER BY sort_order");
$categories = $stmt->fetchAll();

echo json_encode([
    'success' => true,
    'categories' => $categories
]);
?>