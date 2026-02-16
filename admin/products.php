<?php
require_once '../config/database.php';
requireLogin();

// حذف منتج
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
    // حذف الصورة أولاً
    $stmt = $pdo->prepare("SELECT image FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $product = $stmt->fetch();
    
    if ($product && $product['image']) {
        $imagePath = "uploads/products/" . $product['image'];
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }
    }
    
    // حذف المنتج
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
    $stmt->execute([$id]);
    
    header('Location: products.php?msg=deleted');
    exit();
}

// جلب جميع المنتجات مع معلومات القسم والماركة
$stmt = $pdo->query("
    SELECT p.*, c.name_ar as category_name, b.name_ar as brand_name 
    FROM products p
    JOIN categories c ON p.category_id = c.id
    JOIN brands b ON p.brand_id = b.id
    ORDER BY p.created_at DESC
");
$products = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة المنتجات</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/admin-style.css">
</head>
<body>
    <?php include '../includes/sidebar.php'; ?>
    
    <div class="main-content">
        <?php include '../includes/header.php'; ?>
        
        <div class="content">
            <div class="page-header">
                <h1 class="page-title">إدارة المنتجات</h1>
                <a href="product-add.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> إضافة منتج جديد
                </a>
            </div>
            
            <?php if (isset($_GET['msg'])): ?>
                <?php if ($_GET['msg'] == 'added'): ?>
                <div class="alert alert-success">تم إضافة المنتج بنجاح</div>
                <?php elseif ($_GET['msg'] == 'updated'): ?>
                <div class="alert alert-success">تم تحديث المنتج بنجاح</div>
                <?php elseif ($_GET['msg'] == 'deleted'): ?>
                <div class="alert alert-success">تم حذف المنتج بنجاح</div>
                <?php endif; ?>
            <?php endif; ?>
            
            <div class="card">
                <div class="card-body">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>الصورة</th>
                                <th>اسم المنتج</th>
                                <th>القسم</th>
                                <th>الماركة</th>
                                <th>السعر</th>
                                <th>مميز</th>
                                <th>الحالة</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products as $index => $product): ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td>
                                    <?php if ($product['image']): ?>
                                    <img src="uploads/products/<?php echo $product['image']; ?>" width="60" height="60" style="object-fit: cover; border-radius: 8px;">
                                    <?php else: ?>
                                    <div style="width: 60px; height: 60px; background: #f0f0f0; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                        <i class="fas fa-image" style="color: #ccc; font-size: 20px;"></i>
                                    </div>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo $product['name_ar']; ?></td>
                                <td><?php echo $product['category_name']; ?></td>
                                <td><?php echo $product['brand_name']; ?></td>
                                <td>
                                    <?php if ($product['price']): ?>
                                    <?php echo number_format($product['price']); ?> ل.س
                                    <?php else: ?>
                                    -
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($product['is_bestseller']): ?>
                                    <span class="badge badge-success">الأكثر مبيعاً</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($product['is_active']): ?>
                                    <span class="badge badge-success">نشط</span>
                                    <?php else: ?>
                                    <span class="badge badge-danger">غير نشط</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="product-edit.php?id=<?php echo $product['id']; ?>" class="btn-icon" title="تعديل">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="?delete=<?php echo $product['id']; ?>" class="btn-icon" title="حذف" onclick="return confirm('هل أنت متأكد من حذف هذا المنتج؟')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            
                            <?php if (empty($products)): ?>
                            <tr>
                                <td colspan="9" style="text-align: center; padding: 50px;">
                                    <i class="fas fa-box-open" style="font-size: 50px; color: #ccc; margin-bottom: 15px;"></i>
                                    <p>لا توجد منتجات</p>
                                    <a href="product-add.php" class="btn btn-primary">إضافة أول منتج</a>
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <script src="../assets/js/admin-script.js"></script>
</body>
</html>