<?php
// بدلاً من require_once '../config/database.php';

// بدء الجلسة
session_start();

// إذا كان المستخدم مسجل دخوله بالفعل
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit();
}

$error = '';

// تعريف المستخدمين المسموح لهم (بدون قاعدة بيانات)
$valid_users = [
    'admin' => [
        'password' => 'admin123',
        'full_name' => 'مدير النظام',
        'role' => 'admin'
    ],
    // يمكنك إضافة المزيد من المستخدمين هنا
    'manager' => [
        'password' => 'manager123',
        'full_name' => 'مدير المعرض',
        'role' => 'manager'
    ],
    'user' => [
        'password' => 'user123',
        'full_name' => 'مستخدم عادي',
        'role' => 'user'
    ]
];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    // التحقق من وجود المستخدم في المصفوفة
    if (isset($valid_users[$username]) && $valid_users[$username]['password'] === $password) {
        $user = $valid_users[$username];
        
        $_SESSION['user_id'] = $username; // استخدام اسم المستخدم كـ ID
        $_SESSION['username'] = $username;
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_name'] = $user['full_name'];
        
        // تسجيل وقت الدخول
        $_SESSION['last_login'] = date('Y-m-d H:i:s');
        
        header('Location: dashboard.php');
        exit();
    } else {
        $error = 'اسم المستخدم أو كلمة المرور غير صحيحة';
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الدخول - لوحة التحكم</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Tajawal', sans-serif;
        }
        
        @import url('https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700&display=swap');
        
        body {
            background: linear-gradient(135deg, #01396A, #01203f);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        
        .login-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.3);
            width: 100%;
            max-width: 450px;
            padding: 40px;
            animation: slideUp 0.5s ease;
        }
        
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .logo {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .logo-circle {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, #01396A, #01203f);
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0 auto 20px;
        }
        
        .logo-circle i {
            font-size: 50px;
            color: white;
        }
        
        h2 {
            color: #01396A;
            font-size: 28px;
            margin-bottom: 10px;
        }
        
        .error {
            background: #fee;
            color: #c00;
            padding: 12px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 14px;
            text-align: center;
        }
        
        .info-box {
            background: #e8f4fd;
            color: #01396A;
            padding: 12px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 14px;
            text-align: center;
            border-right: 4px solid #01396A;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            color: #333;
            font-weight: 600;
            margin-bottom: 8px;
        }
        
        .input-group {
            position: relative;
        }
        
        .input-group i {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
        }
        
        input {
            width: 100%;
            padding: 15px 45px 15px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 16px;
            transition: all 0.3s;
        }
        
        input:focus {
            outline: none;
            border-color: #01396A;
            box-shadow: 0 0 0 3px rgba(1,57,106,0.1);
        }
        
        button {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #01396A, #01203f);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(1,57,106,0.3);
        }
        
        .footer {
            text-align: center;
            margin-top: 20px;
            color: #666;
            font-size: 14px;
        }
        
        .demo-credentials {
            margin-top: 20px;
            padding: 15px;
            background: #f5f5f5;
            border-radius: 10px;
            font-size: 13px;
        }
        
        .demo-credentials p {
            margin-bottom: 5px;
            color: #01396A;
            font-weight: 600;
        }
        
        .demo-credentials ul {
            list-style: none;
            color: #666;
        }
        
        .demo-credentials li {
            margin-bottom: 3px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo">
            <div class="logo-circle">
                <i class="fas fa-store"></i>
            </div>
            <h2>لوحة التحكم</h2>
            <p>معرض رأفت البهنسي</p>
        </div>
        
        <div class="info-box">
            <i class="fas fa-info-circle"></i> 
            للدخول كمسؤول: admin | admin123
        </div>
        
        <?php if ($error): ?>
        <div class="error">
            <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
        </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label>اسم المستخدم</label>
                <div class="input-group">
                    <i class="fas fa-user"></i>
                    <input type="text" name="username" placeholder="أدخل اسم المستخدم" required>
                </div>
            </div>
            
            <div class="form-group">
                <label>كلمة المرور</label>
                <div class="input-group">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="password" placeholder="أدخل كلمة المرور" required>
                </div>
            </div>
            
            <button type="submit">
                <i class="fas fa-sign-in-alt"></i> تسجيل الدخول
            </button>
        </form>
        
        <div class="demo-credentials">
            <p><i class="fas fa-key"></i> بيانات الدخول المتاحة:</p>
            <ul>
                <li><strong>admin</strong> | admin123 (مدير النظام)</li>
            </ul>
        </div>
        
        <div class="footer">
            جميع الحقوق محفوظة &copy; 2024
        </div>
    </div>
</body>
</html>