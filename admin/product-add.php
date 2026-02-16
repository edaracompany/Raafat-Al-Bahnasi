<?php
require_once '../config/database.php';
requireLogin();

// جلب الأقسام للقائمة المنسدلة
$categories = $pdo->query("SELECT * FROM categories ORDER BY sort_order")->fetchAll();

// جلب الماركات للقائمة المنسدلة
$brands = $pdo->query("SELECT * FROM brands ORDER BY name_ar")->fetchAll();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // رفع الصورة الرئيسية
        $imageName = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $upload = uploadImage($_FILES['image'], 'products');
            if ($upload['success']) {
                $imageName = $upload['filename'];
            } else {
                $error = $upload['message'];
            }
        }
        
        // معالجة المميزات (features)
        $features = [];
        if (!empty($_POST['feature1'])) $features[] = $_POST['feature1'];
        if (!empty($_POST['feature2'])) $features[] = $_POST['feature2'];
        if (!empty($_POST['feature3'])) $features[] = $_POST['feature3'];
        if (!empty($_POST['feature4'])) $features[] = $_POST['feature4'];
        $features_json = json_encode($features);
        
        // إدخال البيانات
        $stmt = $pdo->prepare("
            INSERT INTO products (
                category_id, brand_id, name_ar, name_en, description_ar, 
                price, old_price, image, features, is_bestseller, is_new, 
                has_warranty, warranty_years, stock, is_active
            ) VALUES (
                ?, ?, ?, ?, ?, 
                ?, ?, ?, ?, ?, 
                ?, ?, ?, ?, ?
            )
        ");
        
        $stmt->execute([
            $_POST['category_id'],
            $_POST['brand_id'],
            $_POST['name_ar'],
            $_POST['name_en'] ?: null,
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
        
        header('Location: products.php?msg=added');
        exit();
        
    } catch (PDOException $e) {
        $error = "حدث خطأ: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إضافة منتج جديد</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/admin-style.css">
</head>
<body>
    <?php include '../includes/sidebar.php'; ?>
    
    <div class="main-content">
        <?php include '../includes/header.php'; ?>
        
        <div class="content">
            <div class="page-header">
                <h1 class="page-title">إضافة منتج جديد</h1>
                <a href="products.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-right"></i> عودة للمنتجات
                </a>
            </div>
            
            <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="" enctype="multipart/form-data">
                        <div class="form-row">
                            <div class="form-col">
                                <h3>معلومات أساسية</h3>
                                
                                <div class="form-group">
                                    <label>القسم <span class="required">*</span></label>
                                    <select name="category_id" required>
                                        <option value="">اختر القسم</option>
                                        <?php foreach ($categories as $category): ?>
                                        <option value="<?php echo $category['id']; ?>"><?php echo $category['name_ar']; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label>الماركة <span class="required">*</span></label>
                                    <select name="brand_id" required>
                                        <option value="">اختر الماركة</option>
                                        <?php foreach ($brands as $brand): ?>
                                        <option value="<?php echo $brand['id']; ?>"><?php echo $brand['name_ar']; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label>اسم المنتج (عربي) <span class="required">*</span></label>
                                    <input type="text" name="name_ar" required>
                                </div>
                                
                                <div class="form-group">
                                    <label>اسم المنتج (إنجليزي)</label>
                                    <input type="text" name="name_en">
                                </div>
                                
                                <div class="form-group">
                                    <label>وصف المنتج</label>
                                    <textarea name="description_ar" rows="4"></textarea>
                                </div>
                            </div>
                            
                            <div class="form-col">
                                <h3>السعر والمخزون</h3>
                                
                                <div class="form-group">
                                    <label>السعر</label>
                                    <input type="number" name="price" step="0.01">
                                </div>
                                
                                <div class="form-group">
                                    <label>السعر القديم</label>
                                    <input type="number" name="old_price" step="0.01">
                                </div>
                                
                                <div class="form-group">
                                    <label>المخزون</label>
                                    <input type="number" name="stock" value="0">
                                </div>
                                
                                <h3 style="margin-top: 30px;">المميزات</h3>
                                
                                <div class="form-group">
                                    <label>الميزة 1</label>
                                    <input type="text" name="feature1" placeholder="مثال: تجفيف كامل">
                                </div>
                                
                                <div class="form-group">
                                    <label>الميزة 2</label>
                                    <input type="text" name="feature2" placeholder="مثال: بخار">
                                </div>
                                
                                <div class="form-group">
                                    <label>الميزة 3</label>
                                    <input type="text" name="feature3" placeholder="مثال: إنفرتر">
                                </div>
                                
                                <div class="form-group">
                                    <label>الميزة 4</label>
                                    <input type="text" name="feature4" placeholder="مثال: موتور دائم">
                                </div>
                            </div>
                            
                            <div class="form-col">
                                <h3>الصور</h3>
                                
                                <div class="form-group">
                                    <label>الصورة الرئيسية</label>
                                    <div class="file-input">
                                        <input type="file" name="image" accept="image/*" onchange="previewImage(this)">
                                        <div class="file-preview" id="imagePreview">
                                            <i class="fas fa-cloud-upload-alt"></i>
                                            <p>اضغط لاختيار صورة</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <h3 style="margin-top: 30px;">خيارات إضافية</h3>
                                
                                <div class="checkbox-group">
                                    <label>
                                        <input type="checkbox" name="is_bestseller"> 
                                        الأكثر مبيعاً
                                    </label>
                                </div>
                                
                                <div class="checkbox-group">
                                    <label>
                                        <input type="checkbox" name="is_new" checked> 
                                        منتج جديد
                                    </label>
                                </div>
                                
                                <div class="checkbox-group">
                                    <label>
                                        <input type="checkbox" name="has_warranty" checked> 
                                        يوجد ضمان
                                    </label>
                                </div>
                                
                                <div class="form-group" id="warrantyField">
                                    <label>مدة الضمان (سنوات)</label>
                                    <input type="number" name="warranty_years" value="2" min="1" max="10">
                                </div>
                                
                                <div class="checkbox-group">
                                    <label>
                                        <input type="checkbox" name="is_active" checked> 
                                        المنتج نشط
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-save"></i> حفظ المنتج
                            </button>
                            <a href="products.php" class="btn btn-secondary btn-lg">إلغاء</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <script>
    function previewImage(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.getElementById('imagePreview');
                preview.innerHTML = `<img src="${e.target.result}" style="max-width: 100%; max-height: 200px;">`;
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
    </script>
    
    <script src="../assets/js/admin-script.js"></script>
</body>
</html>