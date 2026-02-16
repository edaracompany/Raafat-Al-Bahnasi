<?php
session_start();

$host = 'localhost';
$dbname = 'rafat_storee';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("فشل الاتصال بقاعدة البيانات: " . $e->getMessage());
}

// التحقق من تسجيل الدخول
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}

// دالة رفع الصور
function uploadImage($file, $folder = 'products') {
    $targetDir = "../admin/uploads/" . $folder . "/";
    if (!file_exists($targetDir)) {
        mkdir($targetDir, 0777, true);
    }
    
    $imageFileType = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
    $fileName = time() . '_' . uniqid() . '.' . $imageFileType;
    $targetFile = $targetDir . $fileName;
    
    // التحقق من صحة الصورة
    $check = getimagesize($file["tmp_name"]);
    if($check === false) {
        return ['success' => false, 'message' => 'الملف ليس صورة صالحة'];
    }
    
    // التحقق من حجم الصورة (5MB كحد أقصى)
    if ($file["size"] > 5000000) {
        return ['success' => false, 'message' => 'حجم الصورة كبير جداً'];
    }
    
    // السماح ببعض الصيغ فقط
    if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif" && $imageFileType != "webp") {
        return ['success' => false, 'message' => 'صيغة الملف غير مسموحة'];
    }
    
    if (move_uploaded_file($file["tmp_name"], $targetFile)) {
        return ['success' => true, 'filename' => $fileName];
    } else {
        return ['success' => false, 'message' => 'حدث خطأ في رفع الملف'];
    }
}

// دالة تنظيف المدخلات
function cleanInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}
?>