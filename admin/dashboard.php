<?php
require_once '../config/database.php';
requireLogin();

// تحديد الصفحة الحالية
$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';

// ==============================================
// معالجة الأقسام (Categories)
// ==============================================
if (isset($_POST['add_category'])) {
    $name_ar = cleanInput($_POST['name_ar']);
    $name_en = cleanInput($_POST['name_en']);
    $slug = cleanInput($_POST['slug']) ?: str_replace(' ', '-', strtolower($name_en));
    $sort_order = (int)$_POST['sort_order'];
    
    $stmt = $pdo->prepare("INSERT INTO categories (name_ar, name_en, slug, sort_order) VALUES (?, ?, ?, ?)");
    $stmt->execute([$name_ar, $name_en, $slug, $sort_order]);
    header('Location: dashboard.php?page=categories&msg=added');
    exit();
}

if (isset($_POST['edit_category'])) {
    $id = (int)$_POST['id'];
    $name_ar = cleanInput($_POST['name_ar']);
    $name_en = cleanInput($_POST['name_en']);
    $slug = cleanInput($_POST['slug']);
    $sort_order = (int)$_POST['sort_order'];
    
    $stmt = $pdo->prepare("UPDATE categories SET name_ar=?, name_en=?, slug=?, sort_order=? WHERE id=?");
    $stmt->execute([$name_ar, $name_en, $slug, $sort_order, $id]);
    header('Location: dashboard.php?page=categories&msg=updated');
    exit();
}

if (isset($_GET['delete_category'])) {
    $id = (int)$_GET['delete_category'];
    $stmt = $pdo->prepare("DELETE FROM categories WHERE id=?");
    $stmt->execute([$id]);
    header('Location: dashboard.php?page=categories&msg=deleted');
    exit();
}

// ==============================================
// معالجة الماركات (Brands)
// ==============================================
if (isset($_POST['add_brand'])) {
    $name_ar = cleanInput($_POST['name_ar']);
    $name_en = cleanInput($_POST['name_en']);
    $slug = cleanInput($_POST['slug']) ?: str_replace(' ', '-', strtolower($name_en));
    
    $stmt = $pdo->prepare("INSERT INTO brands (name_ar, name_en, slug) VALUES (?, ?, ?)");
    $stmt->execute([$name_ar, $name_en, $slug]);
    header('Location: dashboard.php?page=brands&msg=added');
    exit();
}

if (isset($_POST['edit_brand'])) {
    $id = (int)$_POST['id'];
    $name_ar = cleanInput($_POST['name_ar']);
    $name_en = cleanInput($_POST['name_en']);
    $slug = cleanInput($_POST['slug']);
    
    $stmt = $pdo->prepare("UPDATE brands SET name_ar=?, name_en=?, slug=? WHERE id=?");
    $stmt->execute([$name_ar, $name_en, $slug, $id]);
    header('Location: dashboard.php?page=brands&msg=updated');
    exit();
}

if (isset($_GET['delete_brand'])) {
    $id = (int)$_GET['delete_brand'];
    $stmt = $pdo->prepare("DELETE FROM brands WHERE id=?");
    $stmt->execute([$id]);
    header('Location: dashboard.php?page=brands&msg=deleted');
    exit();
}

// ==============================================
// معالجة المنتجات (Products)
// ==============================================
if (isset($_GET['delete_product'])) {
    $id = (int)$_GET['delete_product'];
    
    // حذف الصورة أولاً
    $stmt = $pdo->prepare("SELECT image FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $product = $stmt->fetch();
    
    if ($product && $product['image'] && file_exists("uploads/products/" . $product['image'])) {
        unlink("uploads/products/" . $product['image']);
    }
    
    $stmt = $pdo->prepare("DELETE FROM products WHERE id=?");
    $stmt->execute([$id]);
    header('Location: dashboard.php?page=products&msg=deleted');
    exit();
}

// إضافة منتج جديد
if (isset($_POST['add_product'])) {
    $imageName = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $upload = uploadImage($_FILES['image'], 'products');
        if ($upload['success']) {
            $imageName = $upload['filename'];
        }
    }
    
    $features = [];
    for ($i = 1; $i <= 4; $i++) {
        if (!empty($_POST["feature$i"])) {
            $features[] = $_POST["feature$i"];
        }
    }
    $features_json = json_encode($features);
    
    $stmt = $pdo->prepare("
        INSERT INTO products (
            category_id, brand_id, name_ar, description_ar, 
            price, old_price, image, features, is_bestseller, 
            is_new, has_warranty, warranty_years, stock, is_active
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $_POST['category_id'],
        $_POST['brand_id'],
        $_POST['name_ar'],
        $_POST['description_ar'],
        $_POST['price'] ?: null,
        $_POST['old_price'] ?: null,
        $imageName ?: null,
        $features_json,
        isset($_POST['is_bestseller']) ? 1 : 0,
        isset($_POST['is_new']) ? 1 : 0,
        isset($_POST['has_warranty']) ? 1 : 0,
        $_POST['warranty_years'] ?: 2,
        $_POST['stock'] ?: 0,
        isset($_POST['is_active']) ? 1 : 0
    ]);
    
    header('Location: dashboard.php?page=products&msg=added');
    exit();
}

// تحديث منتج
if (isset($_POST['update_product'])) {
    $product_id = (int)$_POST['product_id'];
    
    // معالجة الصورة الجديدة
    $imageName = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        // حذف الصورة القديمة
        $stmt = $pdo->prepare("SELECT image FROM products WHERE id = ?");
        $stmt->execute([$product_id]);
        $old_image = $stmt->fetchColumn();
        if ($old_image && file_exists("uploads/products/" . $old_image)) {
            unlink("uploads/products/" . $old_image);
        }
        
        // رفع الصورة الجديدة
        $upload = uploadImage($_FILES['image'], 'products');
        if ($upload['success']) {
            $imageName = $upload['filename'];
        }
    }
    
    // معالجة المميزات
    $features = [];
    for ($i = 1; $i <= 4; $i++) {
        if (!empty($_POST["feature$i"])) {
            $features[] = $_POST["feature$i"];
        }
    }
    $features_json = json_encode($features);
    
    if ($imageName) {
        // مع صورة جديدة
        $stmt = $pdo->prepare("
            UPDATE products SET 
                category_id = ?, brand_id = ?, name_ar = ?, description_ar = ?,
                price = ?, old_price = ?, image = ?, features = ?, 
                is_bestseller = ?, is_new = ?, has_warranty = ?, warranty_years = ?, 
                stock = ?, is_active = ?
            WHERE id = ?
        ");
        
        $stmt->execute([
            $_POST['category_id'],
            $_POST['brand_id'],
            $_POST['name_ar'],
            $_POST['description_ar'],
            $_POST['price'] ?: null,
            $_POST['old_price'] ?: null,
            $imageName,
            $features_json,
            isset($_POST['is_bestseller']) ? 1 : 0,
            isset($_POST['is_new']) ? 1 : 0,
            isset($_POST['has_warranty']) ? 1 : 0,
            $_POST['warranty_years'] ?: 2,
            $_POST['stock'] ?: 0,
            isset($_POST['is_active']) ? 1 : 0,
            $product_id
        ]);
    } else {
        // بدون صورة جديدة
        $stmt = $pdo->prepare("
            UPDATE products SET 
                category_id = ?, brand_id = ?, name_ar = ?, description_ar = ?,
                price = ?, old_price = ?, features = ?, 
                is_bestseller = ?, is_new = ?, has_warranty = ?, warranty_years = ?, 
                stock = ?, is_active = ?
            WHERE id = ?
        ");
        
        $stmt->execute([
            $_POST['category_id'],
            $_POST['brand_id'],
            $_POST['name_ar'],
            $_POST['description_ar'],
            $_POST['price'] ?: null,
            $_POST['old_price'] ?: null,
            $features_json,
            isset($_POST['is_bestseller']) ? 1 : 0,
            isset($_POST['is_new']) ? 1 : 0,
            isset($_POST['has_warranty']) ? 1 : 0,
            $_POST['warranty_years'] ?: 2,
            $_POST['stock'] ?: 0,
            isset($_POST['is_active']) ? 1 : 0,
            $product_id
        ]);
    }
    
    header('Location: dashboard.php?page=products&msg=updated');
    exit();
}

// ==============================================
// جلب البيانات حسب الصفحة
// ==============================================
$categories = $pdo->query("SELECT * FROM categories ORDER BY sort_order")->fetchAll();
$brands = $pdo->query("SELECT * FROM brands ORDER BY name_ar")->fetchAll();

// إحصائيات للرئيسية
$stats = [];
$stmt = $pdo->query("SELECT COUNT(*) FROM products WHERE is_active = 1");
$stats['products'] = $stmt->fetchColumn() ?: 0;
$stmt = $pdo->query("SELECT COUNT(*) FROM categories");
$stats['categories'] = $stmt->fetchColumn() ?: 0;
$stmt = $pdo->query("SELECT COUNT(*) FROM brands");
$stats['brands'] = $stmt->fetchColumn() ?: 0;
$stmt = $pdo->query("SELECT COUNT(*) FROM users");
$stats['users'] = $stmt->fetchColumn() ?: 0;

// أحدث المنتجات
$latest_products = $pdo->query("
    SELECT p.*, c.name_ar as category_name, b.name_ar as brand_name 
    FROM products p
    JOIN categories c ON p.category_id = c.id
    JOIN brands b ON p.brand_id = b.id
    WHERE p.is_active = 1
    ORDER BY p.created_at DESC
    LIMIT 5
")->fetchAll();

// جميع المنتجات
$all_products = $pdo->query("
    SELECT p.*, c.name_ar as category_name, b.name_ar as brand_name 
    FROM products p
    JOIN categories c ON p.category_id = c.id
    JOIN brands b ON p.brand_id = b.id
    ORDER BY p.created_at DESC
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">
    <title>لوحة التحكم - معرض رأفت البهنسي</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Tajawal', sans-serif;
        }
        
        @import url('https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap');
        
        :root {
            --primary: #01396A;
            --primary-dark: #01203f;
            --secondary: #6c757d;
            --success: #28a745;
            --danger: #dc3545;
            --warning: #ffc107;
            --info: #17a2b8;
            --light: #f8f9fa;
            --dark: #343a40;
            --white: #ffffff;
            --gray: #e9ecef;
            --sidebar-width: 280px;
        }
        
        body {
            display: flex;
            background: #f4f6f9;
            min-height: 100vh;
            overflow-x: hidden;
            position: relative;
        }
        
        /* ===== Sidebar ===== */
        .sidebar {
            width: var(--sidebar-width);
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: var(--white);
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            transition: all 0.3s ease;
            box-shadow: 2px 0 20px rgba(0,0,0,0.1);
            z-index: 1000;
            right: 0;
            top: 0;
        }
        
        .sidebar.closed {
            right: -280px;
        }
        
        .sidebar-header {
            padding: 25px 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            position: relative;
        }
        
        .close-sidebar {
            position: absolute;
            top: 15px;
            left: 15px;
            width: 35px;
            height: 35px;
            background: rgba(255,255,255,0.2);
            border: none;
            border-radius: 50%;
            color: white;
            font-size: 18px;
            cursor: pointer;
            display: none;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
            z-index: 1001;
        }
        
        .close-sidebar:hover {
            background: rgba(255,255,255,0.3);
            transform: rotate(90deg);
        }
        
        .sidebar-header .logo-circle {
            width: 70px;
            height: 70px;
            background: var(--white);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            font-size: 35px;
            color: var(--primary);
            box-shadow: 0 5px 20px rgba(0,0,0,0.2);
        }
        
        .sidebar-header h3 {
            font-size: 18px;
            margin-bottom: 5px;
            font-weight: 700;
        }
        
        .sidebar-header p {
            font-size: 13px;
            opacity: 0.8;
        }
        
        .sidebar-menu {
            padding: 20px 0;
        }
        
        .sidebar-menu ul {
            list-style: none;
        }
        
        .sidebar-menu li {
            margin-bottom: 5px;
        }
        
        .sidebar-menu li a {
            display: flex;
            align-items: center;
            padding: 15px 25px;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            transition: all 0.3s;
            font-weight: 500;
            border-right: 3px solid transparent;
        }
        
        .sidebar-menu li a:hover,
        .sidebar-menu li.active a {
            background: rgba(255,255,255,0.1);
            color: var(--white);
            border-right-color: var(--white);
        }
        
        .sidebar-menu li a i {
            width: 30px;
            font-size: 18px;
            margin-left: 10px;
        }
        
        /* ===== Main Content ===== */
        .main-content {
            flex: 1;
            margin-right: var(--sidebar-width);
            padding: 20px;
            transition: margin-right 0.3s ease;
            width: 100%;
        }
        
        .main-content.expanded {
            margin-right: 0;
        }
        
        /* ===== Header ===== */
        .header {
            background: var(--white);
            padding: 15px 25px;
            border-radius: 15px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.05);
            margin-bottom: 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .menu-toggle-btn {
            background: none;
            border: none;
            font-size: 24px;
            color: var(--primary);
            cursor: pointer;
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
        }
        
        .menu-toggle-btn:hover {
            background: #f0f0f0;
        }
        
        .header-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 12px;
            background: #f8f9fa;
            padding: 8px 15px;
            border-radius: 50px;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 18px;
        }
        
        .user-details {
            text-align: right;
        }
        
        .user-name {
            font-weight: 700;
            color: var(--dark);
            font-size: 14px;
        }
        
        .user-role {
            font-size: 12px;
            color: var(--secondary);
        }
        
        /* ===== Page Header ===== */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .page-title {
            font-size: 24px;
            color: var(--dark);
            font-weight: 700;
        }
        
        .page-title i {
            margin-left: 10px;
            color: var(--primary);
        }
        
        /* ===== Buttons ===== */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 12px 25px;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: all 0.3s;
            min-width: 120px;
        }
        
        .btn-sm {
            padding: 8px 15px;
            min-width: auto;
        }
        
        .btn-primary {
            background: var(--primary);
            color: white;
        }
        
        .btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(1,57,106,0.3);
        }
        
        .btn-success {
            background: var(--success);
            color: white;
        }
        
        .btn-danger {
            background: var(--danger);
            color: white;
        }
        
        .btn-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 35px;
            height: 35px;
            border-radius: 8px;
            background: none;
            color: var(--secondary);
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .btn-icon:hover {
            background: #f0f0f0;
            color: var(--primary);
        }
        
        /* ===== Stats Grid ===== */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: var(--white);
            border-radius: 20px;
            padding: 25px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.03);
            display: flex;
            align-items: center;
            gap: 15px;
            transition: all 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(1,57,106,0.1);
        }
        
        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            color: white;
            flex-shrink: 0;
        }
        
        .stat-details h3 {
            font-size: 28px;
            font-weight: 800;
            color: var(--dark);
            margin-bottom: 5px;
        }
        
        .stat-details p {
            color: var(--secondary);
            font-size: 14px;
            font-weight: 500;
        }
        
        /* ===== Cards ===== */
        .card {
            background: var(--white);
            border-radius: 20px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.03);
            margin-bottom: 25px;
            overflow: hidden;
        }
        
        .card-header {
            padding: 20px 25px;
            border-bottom: 1px solid rgba(0,0,0,0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .card-header h2 {
            font-size: 18px;
            font-weight: 700;
            color: var(--dark);
        }
        
        .card-header h2 i {
            margin-left: 10px;
            color: var(--primary);
        }
        
        .card-body {
            padding: 20px 25px;
            overflow-x: auto;
        }
        
        /* ===== Table ===== */
        .table-container {
            overflow-x: auto;
            border-radius: 15px;
        }
        
        .table {
            width: 100%;
            border-collapse: collapse;
            min-width: 600px;
        }
        
        .table thead {
            background: #f8f9fa;
        }
        
        .table thead th {
            padding: 15px;
            font-weight: 600;
            color: var(--dark);
            border-bottom: 2px solid #e9ecef;
            font-size: 14px;
            white-space: nowrap;
        }
        
        .table tbody td {
            padding: 15px;
            border-bottom: 1px solid #e9ecef;
            color: #495057;
            font-size: 14px;
        }
        
        .table tbody tr:hover {
            background: #f8f9fa;
        }
        
        /* ===== Forms ===== */
        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--dark);
            font-size: 14px;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e9ecef;
            border-radius: 12px;
            font-size: 14px;
            transition: all 0.3s;
        }
        
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(1,57,106,0.1);
        }
        
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
        }
        
        .checkbox-group label {
            display: flex;
            align-items: center;
            gap: 5px;
            font-weight: 500;
        }
        
        /* ===== Alerts ===== */
        .alert {
            padding: 15px 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border-right: 4px solid var(--success);
        }
        
        /* ===== Empty State ===== */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: var(--secondary);
        }
        
        .empty-state i {
            font-size: 60px;
            opacity: 0.3;
            margin-bottom: 20px;
        }
        
        .empty-state p {
            font-size: 16px;
            margin-bottom: 20px;
        }
        
        /* ===== Modal ===== */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 2000;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        
        .modal.active {
            display: flex;
        }
        
        .modal-content {
            background: white;
            border-radius: 25px;
            width: 100%;
            max-width: 500px;
            max-height: 90vh;
            overflow-y: auto;
            animation: slideDown 0.3s;
        }
        
        @keyframes slideDown {
            from {
                transform: translateY(-50px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
        
        .modal-header {
            padding: 20px 25px;
            border-bottom: 1px solid #e9ecef;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            background: white;
            border-radius: 25px 25px 0 0;
        }
        
        .modal-header h3 {
            font-size: 18px;
            color: var(--dark);
        }
        
        .modal-close {
            width: 35px;
            height: 35px;
            border-radius: 10px;
            border: none;
            background: #f0f0f0;
            color: var(--secondary);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            transition: all 0.3s;
        }
        
        .modal-close:hover {
            background: var(--danger);
            color: white;
        }
        
        .modal-body {
            padding: 25px;
        }
        
        .modal-footer {
            padding: 20px 25px;
            border-top: 1px solid #e9ecef;
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }
        
        /* ===== Responsive ===== */
        @media screen and (max-width: 1200px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .form-grid {
                grid-template-columns: 1fr;
            }
        }
        
        @media screen and (max-width: 992px) {
            .sidebar {
                right: -280px;
            }
            
            .sidebar.active {
                right: 0;
            }
            
            .close-sidebar {
                display: flex;
            }
            
            .main-content {
                margin-right: 0;
            }
            
            .main-content.sidebar-open {
                margin-right: 0;
            }
            
            .menu-toggle-btn {
                display: block;
            }
        }
        
        @media screen and (max-width: 768px) {
            .main-content {
                padding: 15px;
            }
            
            .header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .header-right {
                width: 100%;
            }
            
            .user-info {
                width: 100%;
                justify-content: center;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
                gap: 15px;
            }
            
            .stat-card {
                padding: 20px;
            }
            
            .page-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .btn {
                width: 100%;
            }
            
            .card-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .modal-content {
                margin: 10px;
                max-height: 85vh;
            }
        }
        
        @media screen and (max-width: 480px) {
            .stat-icon {
                width: 50px;
                height: 50px;
                font-size: 22px;
            }
            
            .stat-details h3 {
                font-size: 22px;
            }
            
            .page-title {
                font-size: 20px;
            }
            
            .card-header h2 {
                font-size: 16px;
            }
            
            .card-body {
                padding: 15px;
            }
        }
        
        /* تحسينات للمس */
        @media (hover: none) and (pointer: coarse) {
            .stat-card:hover,
            .btn:hover {
                transform: none;
            }
            
            .sidebar-menu li a {
                padding: 18px 25px;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <button class="close-sidebar" id="closeSidebar">
                <i class="fas fa-times"></i>
            </button>
            <div class="logo-circle">
                <i class="fas fa-store"></i>
            </div>
            <h3>معرض رأفت البهنسي</h3>
            <p>لوحة التحكم</p>
        </div>
        
        <div class="sidebar-menu">
            <ul>
                <li class="<?php echo $page == 'dashboard' ? 'active' : ''; ?>">
                    <a href="?page=dashboard">
                        <i class="fas fa-home"></i>
                        <span>الرئيسية</span>
                    </a>
                </li>
                <li class="<?php echo $page == 'categories' ? 'active' : ''; ?>">
                    <a href="?page=categories">
                        <i class="fas fa-tags"></i>
                        <span>الأقسام</span>
                    </a>
                </li>
                <li class="<?php echo $page == 'brands' ? 'active' : ''; ?>">
                    <a href="?page=brands">
                        <i class="fas fa-star"></i>
                        <span>الماركات</span>
                    </a>
                </li>
                <li class="<?php echo $page == 'products' ? 'active' : ''; ?>">
                    <a href="?page=products">
                        <i class="fas fa-box"></i>
                        <span>المنتجات</span>
                    </a>
                </li>
                <li class="<?php echo $page == 'add-product' ? 'active' : ''; ?>">
                    <a href="?page=add-product">
                        <i class="fas fa-plus-circle"></i>
                        <span>إضافة منتج</span>
                    </a>
                </li>
                <li>
                    <a href="logout.php">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>تسجيل خروج</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="main-content" id="mainContent">
        <div class="header">
            <div class="header-left">
                <button class="menu-toggle-btn" id="menuToggle">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
            
            <div class="header-right">
                <div class="user-info">
                    <div class="user-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="user-details">
                        <div class="user-name"><?php echo $_SESSION['user_name'] ?? 'مدير النظام'; ?></div>
                        <div class="user-role"><?php echo $_SESSION['user_role'] ?? 'admin'; ?></div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="content">
            <?php if (isset($_GET['msg'])): ?>
                <?php if ($_GET['msg'] == 'added'): ?>
                    <div class="alert alert-success"><i class="fas fa-check-circle"></i> تمت الإضافة بنجاح</div>
                <?php elseif ($_GET['msg'] == 'updated'): ?>
                    <div class="alert alert-success"><i class="fas fa-check-circle"></i> تم التحديث بنجاح</div>
                <?php elseif ($_GET['msg'] == 'deleted'): ?>
                    <div class="alert alert-success"><i class="fas fa-check-circle"></i> تم الحذف بنجاح</div>
                <?php endif; ?>
            <?php endif; ?>
            
            <?php if ($page == 'dashboard'): ?>
                <!-- الصفحة الرئيسية -->
                <h1 class="page-title">
                    <i class="fas fa-tachometer-alt"></i>
                    مرحباً <?php echo $_SESSION['user_name'] ?? 'مدير النظام'; ?> 👋
                </h1>
                
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon" style="background: #01396A;">
                            <i class="fas fa-box"></i>
                        </div>
                        <div class="stat-details">
                            <h3><?php echo $stats['products']; ?></h3>
                            <p>إجمالي المنتجات</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon" style="background: #28a745;">
                            <i class="fas fa-tags"></i>
                        </div>
                        <div class="stat-details">
                            <h3><?php echo $stats['categories']; ?></h3>
                            <p>الأقسام</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon" style="background: #ffc107;">
                            <i class="fas fa-star"></i>
                        </div>
                        <div class="stat-details">
                            <h3><?php echo $stats['brands']; ?></h3>
                            <p>الماركات</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon" style="background: #17a2b8;">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-details">
                            <h3><?php echo $stats['users']; ?></h3>
                            <p>المستخدمين</p>
                        </div>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <h2><i class="fas fa-clock"></i> أحدث المنتجات</h2>
                        <a href="?page=products" class="btn btn-primary btn-sm">
                            <i class="fas fa-arrow-left"></i> عرض الكل
                        </a>
                    </div>
                    <div class="card-body">
                        <?php if (empty($latest_products)): ?>
                            <div class="empty-state">
                                <i class="fas fa-box-open"></i>
                                <p>لا توجد منتجات حالياً</p>
                                <a href="?page=add-product" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> إضافة منتج جديد
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="table-container">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>الصورة</th>
                                            <th>اسم المنتج</th>
                                            <th>القسم</th>
                                            <th>الماركة</th>
                                            <th>تاريخ الإضافة</th>
                                            <th>الإجراءات</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($latest_products as $product): ?>
                                        <tr>
                                            <td>
                                                <?php if ($product['image'] && file_exists("uploads/products/" . $product['image'])): ?>
                                                    <img src="uploads/products/<?php echo $product['image']; ?>" width="50" height="50" style="object-fit: cover; border-radius: 10px;">
                                                <?php else: ?>
                                                    <div style="width: 50px; height: 50px; background: #f0f0f0; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                                                        <i class="fas fa-image" style="color: #ccc;"></i>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($product['name_ar']); ?></td>
                                            <td><?php echo htmlspecialchars($product['category_name']); ?></td>
                                            <td><?php echo htmlspecialchars($product['brand_name']); ?></td>
                                            <td><?php echo date('Y-m-d', strtotime($product['created_at'])); ?></td>
                                            <td>
                                                <button class="btn-icon" onclick='editProduct(<?php echo json_encode($product); ?>)' title="تعديل">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <a href="?delete_product=<?php echo $product['id']; ?>" class="btn-icon" title="حذف" onclick="return confirm('هل أنت متأكد؟')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
            <?php elseif ($page == 'categories'): ?>
                <!-- إدارة الأقسام -->
                <div class="page-header">
                    <h1 class="page-title"><i class="fas fa-tags"></i> إدارة الأقسام</h1>
                    <button class="btn btn-primary" onclick="openModal('addCategoryModal')">
                        <i class="fas fa-plus"></i> إضافة قسم جديد
                    </button>
                </div>
                
                <div class="card">
                    <div class="card-body">
                        <div class="table-container">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>الاسم (عربي)</th>
                                        <th>الاسم (إنجليزي)</th>
                                        <th>الرابط</th>
                                        <th>الترتيب</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($categories as $index => $cat): ?>
                                    <tr>
                                        <td><?php echo $index + 1; ?></td>
                                        <td><?php echo $cat['name_ar']; ?></td>
                                        <td><?php echo $cat['name_en']; ?></td>
                                        <td><?php echo $cat['slug']; ?></td>
                                        <td><?php echo $cat['sort_order']; ?></td>
                                        <td>
                                            <button class="btn-icon" onclick='editCategory(<?php echo json_encode($cat); ?>)' title="تعديل">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <a href="?delete_category=<?php echo $cat['id']; ?>&page=categories" class="btn-icon" title="حذف" onclick="return confirm('هل أنت متأكد؟')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
            <?php elseif ($page == 'brands'): ?>
                <!-- إدارة الماركات -->
                <div class="page-header">
                    <h1 class="page-title"><i class="fas fa-star"></i> إدارة الماركات</h1>
                    <button class="btn btn-primary" onclick="openModal('addBrandModal')">
                        <i class="fas fa-plus"></i> إضافة ماركة جديدة
                    </button>
                </div>
                
                <div class="card">
                    <div class="card-body">
                        <div class="table-container">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>الاسم (عربي)</th>
                                        <th>الاسم (إنجليزي)</th>
                                        <th>الرابط</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($brands as $index => $brand): ?>
                                    <tr>
                                        <td><?php echo $index + 1; ?></td>
                                        <td><?php echo $brand['name_ar']; ?></td>
                                        <td><?php echo $brand['name_en']; ?></td>
                                        <td><?php echo $brand['slug']; ?></td>
                                        <td>
                                            <button class="btn-icon" onclick='editBrand(<?php echo json_encode($brand); ?>)' title="تعديل">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <a href="?delete_brand=<?php echo $brand['id']; ?>&page=brands" class="btn-icon" title="حذف" onclick="return confirm('هل أنت متأكد؟')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
            <?php elseif ($page == 'products'): ?>
                <!-- إدارة المنتجات -->
                <div class="page-header">
                    <h1 class="page-title"><i class="fas fa-box"></i> إدارة المنتجات</h1>
                    <a href="?page=add-product" class="btn btn-primary">
                        <i class="fas fa-plus"></i> إضافة منتج جديد
                    </a>
                </div>
                
                <div class="card">
                    <div class="card-body">
                        <div class="table-container">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>الصورة</th>
                                        <th>اسم المنتج</th>
                                        <th>القسم</th>
                                        <th>الماركة</th>
                                        <th>السعر</th>
                                        <th>الحالة</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($all_products as $product): ?>
                                    <tr>
                                        <td>
                                            <?php if ($product['image'] && file_exists("uploads/products/" . $product['image'])): ?>
                                                <img src="uploads/products/<?php echo $product['image']; ?>" width="50" height="50" style="object-fit: cover; border-radius: 10px;">
                                            <?php else: ?>
                                                <div style="width: 50px; height: 50px; background: #f0f0f0; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                                                    <i class="fas fa-image"></i>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo $product['name_ar']; ?></td>
                                        <td><?php echo $product['category_name']; ?></td>
                                        <td><?php echo $product['brand_name']; ?></td>
                                        <td><?php echo $product['price'] ? number_format($product['price']) . ' ل.س' : '-'; ?></td>
                                        <td>
                                            <?php if ($product['is_active']): ?>
                                                <span style="color: #28a745;"><i class="fas fa-check-circle"></i> نشط</span>
                                            <?php else: ?>
                                                <span style="color: #dc3545;"><i class="fas fa-times-circle"></i> غير نشط</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <button class="btn-icon" onclick='editProduct(<?php echo json_encode($product); ?>)' title="تعديل">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <a href="?delete_product=<?php echo $product['id']; ?>&page=products" class="btn-icon" title="حذف" onclick="return confirm('هل أنت متأكد؟')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
            <?php elseif ($page == 'add-product'): ?>
                <!-- إضافة منتج جديد -->
                <div class="page-header">
                    <h1 class="page-title"><i class="fas fa-plus-circle"></i> إضافة منتج جديد</h1>
                    <a href="?page=products" class="btn btn-primary">
                        <i class="fas fa-arrow-left"></i> عودة للمنتجات
                    </a>
                </div>
                
                <div class="card">
                    <div class="card-body">
                        <form method="POST" enctype="multipart/form-data">
                            <div class="form-grid">
                                <div>
                                    <h3 style="margin-bottom: 20px;">معلومات أساسية</h3>
                                    
                                    <div class="form-group">
                                        <label>القسم</label>
                                        <select name="category_id" required>
                                            <option value="">اختر القسم</option>
                                            <?php foreach ($categories as $cat): ?>
                                            <option value="<?php echo $cat['id']; ?>"><?php echo $cat['name_ar']; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label>الماركة</label>
                                        <select name="brand_id" required>
                                            <option value="">اختر الماركة</option>
                                            <?php foreach ($brands as $brand): ?>
                                            <option value="<?php echo $brand['id']; ?>"><?php echo $brand['name_ar']; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label>اسم المنتج</label>
                                        <input type="text" name="name_ar" required>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label>وصف المنتج</label>
                                        <textarea name="description_ar" rows="4"></textarea>
                                    </div>
                                </div>
                                
                                <div>
                                    <h3 style="margin-bottom: 20px;">السعر والصورة</h3>
                                    
                                    <div class="form-group">
                                        <label>السعر</label>
                                        <input type="number" name="price" step="0.01">
                                    </div>
                                    
                                    <div class="form-group">
                                        <label>السعر القديم</label>
                                        <input type="number" name="old_price" step="0.01">
                                    </div>
                                    
                                    <div class="form-group">
                                        <label>صورة المنتج</label>
                                        <input type="file" name="image" accept="image/*">
                                    </div>
                                    
                                    <h3 style="margin: 20px 0;">المميزات</h3>
                                    
                                    <div class="form-group">
                                        <input type="text" name="feature1" placeholder="الميزة 1">
                                    </div>
                                    
                                    <div class="form-group">
                                        <input type="text" name="feature2" placeholder="الميزة 2">
                                    </div>
                                    
                                    <div class="form-group">
                                        <input type="text" name="feature3" placeholder="الميزة 3">
                                    </div>
                                    
                                    <div class="form-group">
                                        <input type="text" name="feature4" placeholder="الميزة 4">  
                                    </div>
                                </div>
                            </div>
                            
                            <h3 style="margin: 20px 0;">خيارات إضافية</h3>
                            
                            <div class="checkbox-group">
                                <label>
                                    <input type="checkbox" name="is_bestseller"> الأكثر مبيعاً
                                </label>
                                <label>
                                    <input type="checkbox" name="is_new" checked> منتج جديد
                                </label>
                                <label>
                                    <input type="checkbox" name="has_warranty" checked> يوجد ضمان
                                </label>
                                <label>
                                    <input type="checkbox" name="is_active" checked> المنتج نشط
                                </label>
                            </div>
                            
                            <div style="margin-top: 30px;">
                                <button type="submit" name="add_product" class="btn btn-primary">
                                    <i class="fas fa-save"></i> حفظ المنتج
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- مودال إضافة قسم -->
    <div class="modal" id="addCategoryModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>إضافة قسم جديد</h3>
                <button class="modal-close" onclick="closeModal('addCategoryModal')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <div class="form-group">
                        <label>اسم القسم (عربي)</label>
                        <input type="text" name="name_ar" required>
                    </div>
                    <div class="form-group">
                        <label>اسم القسم (إنجليزي)</label>
                        <input type="text" name="name_en" required>
                    </div>
                    <div class="form-group">
                        <label>الرابط (slug)</label>
                        <input type="text" name="slug" placeholder="اتركه فارغاً للتوليد التلقائي">
                    </div>
                    <div class="form-group">
                        <label>ترتيب العرض</label>
                        <input type="number" name="sort_order" value="0">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn" onclick="closeModal('addCategoryModal')">إلغاء</button>
                    <button type="submit" name="add_category" class="btn btn-success">حفظ</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- مودال تعديل قسم -->
    <div class="modal" id="editCategoryModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>تعديل القسم</h3>
                <button class="modal-close" onclick="closeModal('editCategoryModal')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form method="POST">
                <input type="hidden" name="id" id="edit_cat_id">
                <div class="modal-body">
                    <div class="form-group">
                        <label>اسم القسم (عربي)</label>
                        <input type="text" name="name_ar" id="edit_cat_name_ar" required>
                    </div>
                    <div class="form-group">
                        <label>اسم القسم (إنجليزي)</label>
                        <input type="text" name="name_en" id="edit_cat_name_en" required>
                    </div>
                    <div class="form-group">
                        <label>الرابط (slug)</label>
                        <input type="text" name="slug" id="edit_cat_slug" required>
                    </div>
                    <div class="form-group">
                        <label>ترتيب العرض</label>
                        <input type="number" name="sort_order" id="edit_cat_sort_order">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn" onclick="closeModal('editCategoryModal')">إلغاء</button>
                    <button type="submit" name="edit_category" class="btn btn-success">تحديث</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- مودال إضافة ماركة -->
    <div class="modal" id="addBrandModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>إضافة ماركة جديدة</h3>
                <button class="modal-close" onclick="closeModal('addBrandModal')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <div class="form-group">
                        <label>اسم الماركة (عربي)</label>
                        <input type="text" name="name_ar" required>
                    </div>
                    <div class="form-group">
                        <label>اسم الماركة (إنجليزي)</label>
                        <input type="text" name="name_en" required>
                    </div>
                    <div class="form-group">
                        <label>الرابط (slug)</label>
                        <input type="text" name="slug" placeholder="اتركه فارغاً للتوليد التلقائي">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn" onclick="closeModal('addBrandModal')">إلغاء</button>
                    <button type="submit" name="add_brand" class="btn btn-success">حفظ</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- مودال تعديل ماركة -->
    <div class="modal" id="editBrandModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>تعديل الماركة</h3>
                <button class="modal-close" onclick="closeModal('editBrandModal')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form method="POST">
                <input type="hidden" name="id" id="edit_brand_id">
                <div class="modal-body">
                    <div class="form-group">
                        <label>اسم الماركة (عربي)</label>
                        <input type="text" name="name_ar" id="edit_brand_name_ar" required>
                    </div>
                    <div class="form-group">
                        <label>اسم الماركة (إنجليزي)</label>
                        <input type="text" name="name_en" id="edit_brand_name_en" required>
                    </div>
                    <div class="form-group">
                        <label>الرابط (slug)</label>
                        <input type="text" name="slug" id="edit_brand_slug" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn" onclick="closeModal('editBrandModal')">إلغاء</button>
                    <button type="submit" name="edit_brand" class="btn btn-success">تحديث</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- مودال تعديل منتج -->
    <div class="modal" id="editProductModal">
        <div class="modal-content" style="max-width: 800px;">
            <div class="modal-header">
                <h3><i class="fas fa-edit"></i> تعديل المنتج</h3>
                <button class="modal-close" onclick="closeModal('editProductModal')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="product_id" id="edit_product_id">
                <div class="modal-body">
                    <div class="form-grid" style="grid-template-columns: 1fr 1fr; gap: 20px;">
                        <!-- العمود الأيمن -->
                        <div>
                            <h4 style="margin-bottom: 15px; color: var(--primary);">معلومات أساسية</h4>
                            
                            <div class="form-group">
                                <label>القسم <span style="color: red;">*</span></label>
                                <select name="category_id" id="edit_category_id" required>
                                    <option value="">اختر القسم</option>
                                    <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>"><?php echo $cat['name_ar']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label>الماركة <span style="color: red;">*</span></label>
                                <select name="brand_id" id="edit_brand_id" required>
                                    <option value="">اختر الماركة</option>
                                    <?php foreach ($brands as $brand): ?>
                                    <option value="<?php echo $brand['id']; ?>"><?php echo $brand['name_ar']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label>اسم المنتج <span style="color: red;">*</span></label>
                                <input type="text" name="name_ar" id="edit_name_ar" required>
                            </div>
                            
                            <div class="form-group">
                                <label>وصف المنتج</label>
                                <textarea name="description_ar" id="edit_description_ar" rows="3"></textarea>
                            </div>
                        </div>
                        
                        <!-- العمود الأيسر -->
                        <div>
                            <h4 style="margin-bottom: 15px; color: var(--primary);">السعر والمميزات</h4>
                            
                            <div class="form-group">
                                <label>السعر</label>
                                <input type="number" name="price" step="0.01" id="edit_price">
                            </div>
                            
                            <div class="form-group">
                                <label>السعر القديم</label>
                                <input type="number" name="old_price" step="0.01" id="edit_old_price">
                            </div>
                            
                            <div class="form-group">
                                <label>صورة المنتج</label>
                                <input type="file" name="image" accept="image/*">
                                <div id="current_image" style="margin-top: 10px;"></div>
                            </div>
                            
                            <h4 style="margin: 15px 0 10px; color: var(--primary);">المميزات</h4>
                            
                            <div class="form-group">
                                <input type="text" name="feature1" id="edit_feature1" placeholder="الميزة 1">
                            </div>
                            <div class="form-group">
                                <input type="text" name="feature2" id="edit_feature2" placeholder="الميزة 2">
                            </div>
                            <div class="form-group">
                                <input type="text" name="feature3" id="edit_feature3" placeholder="الميزة 3">
                            </div>
                            <div class="form-group">
                                <input type="text" name="feature4" id="edit_feature4" placeholder="الميزة 4">
                            </div>
                        </div>
                    </div>
                    
                    <!-- خيارات إضافية -->
                    <h4 style="margin: 20px 0 15px; color: var(--primary);">خيارات إضافية</h4>
                    
                    <div class="checkbox-group" style="display: flex; flex-wrap: wrap; gap: 20px;">
                        <label style="display: flex; align-items: center; gap: 5px;">
                            <input type="checkbox" name="is_bestseller" id="edit_is_bestseller"> الأكثر مبيعاً
                        </label>
                        <label style="display: flex; align-items: center; gap: 5px;">
                            <input type="checkbox" name="is_new" id="edit_is_new"> منتج جديد
                        </label>
                        <label style="display: flex; align-items: center; gap: 5px;">
                            <input type="checkbox" name="has_warranty" id="edit_has_warranty"> يوجد ضمان
                        </label>
                        <label style="display: flex; align-items: center; gap: 5px;">
                            <input type="checkbox" name="is_active" id="edit_is_active" checked> المنتج نشط
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn" onclick="closeModal('editProductModal')">إلغاء</button>
                    <button type="submit" name="update_product" class="btn btn-success">
                        <i class="fas fa-save"></i> حفظ التغييرات
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        // ===== Sidebar =====
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('mainContent');
        const menuToggle = document.getElementById('menuToggle');
        const closeSidebar = document.getElementById('closeSidebar');
        
        function toggleSidebar() {
            sidebar.classList.toggle('active');
            if (window.innerWidth <= 992) {
                document.body.style.overflow = sidebar.classList.contains('active') ? 'hidden' : '';
            }
        }
        
        function closeSidebarFunc() {
            sidebar.classList.remove('active');
            document.body.style.overflow = '';
        }
        
        menuToggle.addEventListener('click', toggleSidebar);
        closeSidebar.addEventListener('click', closeSidebarFunc);
        
        // إغلاق القائمة عند النقر خارجها
        document.addEventListener('click', function(event) {
            if (window.innerWidth <= 992) {
                if (!sidebar.contains(event.target) && !menuToggle.contains(event.target)) {
                    sidebar.classList.remove('active');
                    document.body.style.overflow = '';
                }
            }
        });
        
        // ضبط حجم الشاشة
        window.addEventListener('resize', function() {
            if (window.innerWidth > 992) {
                sidebar.classList.remove('active');
                document.body.style.overflow = '';
            }
        });
        
        // ===== Modal Functions =====
        function openModal(modalId) {
            document.getElementById(modalId).classList.add('active');
            document.body.style.overflow = 'hidden';
        }
        
        function closeModal(modalId) {
            document.getElementById(modalId).classList.remove('active');
            document.body.style.overflow = '';
        }
        
        // ===== Edit Functions =====
        function editCategory(category) {
            document.getElementById('edit_cat_id').value = category.id;
            document.getElementById('edit_cat_name_ar').value = category.name_ar;
            document.getElementById('edit_cat_name_en').value = category.name_en;
            document.getElementById('edit_cat_slug').value = category.slug;
            document.getElementById('edit_cat_sort_order').value = category.sort_order;
            openModal('editCategoryModal');
        }
        
        function editBrand(brand) {
            document.getElementById('edit_brand_id').value = brand.id;
            document.getElementById('edit_brand_name_ar').value = brand.name_ar;
            document.getElementById('edit_brand_name_en').value = brand.name_en;
            document.getElementById('edit_brand_slug').value = brand.slug;
            openModal('editBrandModal');
        }
        
        function editProduct(product) {
            console.log('تحرير المنتج:', product);
            
            document.getElementById('edit_product_id').value = product.id;
            document.getElementById('edit_category_id').value = product.category_id;
            document.getElementById('edit_brand_id').value = product.brand_id;
            document.getElementById('edit_name_ar').value = product.name_ar;
            document.getElementById('edit_description_ar').value = product.description_ar || '';
            document.getElementById('edit_price').value = product.price || '';
            document.getElementById('edit_old_price').value = product.old_price || '';
            
            document.getElementById('edit_is_bestseller').checked = product.is_bestseller == 1;
            document.getElementById('edit_is_new').checked = product.is_new == 1;
            document.getElementById('edit_has_warranty').checked = product.has_warranty == 1;
            document.getElementById('edit_is_active').checked = product.is_active == 1;
            
            let features = [];
            try {
                if (product.features) {
                    features = JSON.parse(product.features);
                }
            } catch(e) {
                console.log('خطأ في قراءة المميزات:', e);
                features = [];
            }
            
            if (!Array.isArray(features)) {
                features = [];
            }
            
            document.getElementById('edit_feature1').value = features[0] || '';
            document.getElementById('edit_feature2').value = features[1] || '';
            document.getElementById('edit_feature3').value = features[2] || '';
            document.getElementById('edit_feature4').value = features[3] || '';
            
            const imageContainer = document.getElementById('current_image');
            if (product.image) {
                imageContainer.innerHTML = `
                    <div style="margin-top: 10px;">
                        <img src="uploads/products/${product.image}" style="width: 100px; height: 100px; object-fit: cover; border-radius: 10px; border: 1px solid #ddd;">
                        <p style="font-size: 12px; color: #666; margin-top: 5px;">الصورة الحالية</p>
                    </div>
                `;
            } else {
                imageContainer.innerHTML = '';
            }
            
            openModal('editProductModal');
        }
        
        // إغلاق المودال عند النقر خارجه
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.classList.remove('active');
                document.body.style.overflow = '';
            }
        };
        
        // توليد slug تلقائياً
        document.querySelector('input[name="name_en"]')?.addEventListener('input', function() {
            let slugInput = document.querySelector('input[name="slug"]');
            if (slugInput && !slugInput.value) {
                slugInput.value = this.value.toLowerCase().replace(/\s+/g, '-');
            }
        });
    </script>
</body>
</html>