<?php
require_once 'config/database.php';

// كلمة السر الجديدة
$new_password = 'admin123';

// تشفيرها بطريقة صحيحة
$hashed = password_hash($new_password, PASSWORD_DEFAULT);

echo "🔑 كلمة السر الجديدة: $new_password<br>";
echo "🔐 التشفير الجديد: $hashed<br>";
echo "<hr>";

// تحديث قاعدة البيانات
$stmt = $pdo->prepare("UPDATE users SET password = ? WHERE username = 'admin'");
if($stmt->execute([$hashed])) {
    echo "✅ تم تحديث كلمة السر بنجاح!<br>";
    
    // التحقق
    $check = $pdo->query("SELECT password FROM users WHERE username = 'admin'")->fetch();
    if(password_verify('admin123', $check['password'])) {
        echo "✅ <strong style='color:green; font-size:20px;'>كلمة السر تعمل الآن!</strong><br>";
        echo "يمكنك تسجيل الدخول بـ:<br>";
        echo "👤 اسم المستخدم: admin<br>";
        echo "🔑 كلمة السر: admin123<br>";
        echo "<hr>";
        echo "<a href='admin/login.php' target='_blank' style='background:#01396A; color:white; padding:10px 20px; text-decoration:none; border-radius:5px;'>➡️ اضغط هنا لتسجيل الدخول</a>";
    } else {
        echo "❌ لسه في مشكلة بالتشفير!";
    }
} else {
    echo "❌ فشل التحديث";
}
?>