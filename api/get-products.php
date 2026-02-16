<?php
header('Content-Type: application/json');
require_once '../config/database.php';

$category = isset($_GET['category']) ? $_GET['category'] : 'all';
$brand = isset($_GET['brand']) ? $_GET['brand'] : 'all';
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
$offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;

$sql = "
    SELECT p.*, c.name_ar as category_name, c.slug as category_slug, 
           b.name_ar as brand_name, b.slug as brand_slug
    FROM products p
    JOIN categories c ON p.category_id = c.id
    JOIN brands b ON p.brand_id = b.id
    WHERE p.is_active = 1
";

$params = [];

if ($category !== 'all') {
    $sql .= " AND c.slug = ?";
    $params[] = $category;
}

if ($brand !== 'all') {
    $sql .= " AND b.slug = ?";
    $params[] = $brand;
}

$sql .= " ORDER BY p.is_bestseller DESC, p.created_at DESC LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

// معالجة المميزات
foreach ($products as &$product) {
    $product['features'] = json_decode($product['features'], true) ?: [];
}

echo json_encode([
    'success' => true,
    'products' => $products,
    'total' => count($products)
]);
?>