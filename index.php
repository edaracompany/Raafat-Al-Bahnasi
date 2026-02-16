<?php
    // الاتصال بقاعدة البيانات
    require_once 'config/database.php';
    
    // جلب الأقسام
    $categories = $pdo->query("SELECT * FROM categories ORDER BY sort_order")->fetchAll();
    
    // جلب الماركات
    $brands = $pdo->query("SELECT * FROM brands ORDER BY name_ar")->fetchAll();
    
    // جلب المنتجات (المنتجات النشطة فقط)
    $products = $pdo->query("
        SELECT p.*, c.name_ar as category_name, c.slug as category_slug, 
               b.name_ar as brand_name, b.slug as brand_slug
        FROM products p
        JOIN categories c ON p.category_id = c.id
        JOIN brands b ON p.brand_id = b.id
        WHERE p.is_active = 1
        ORDER BY p.is_bestseller DESC, p.created_at DESC
    ")->fetchAll();
    ?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes">
    <title>معرض رأفت البهنسي للأجهزة المنزلية</title>
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
      
 * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Tajawal', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        /* import Arabic font */
        @import url('https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap');

        :root {
            --primary: #01396A;
            --white: #FFFFFF;
            --light-bg: #f0f4f8;
            --gray: #e0e0e0;
            --dark-gray: #4a4a4a;
            --whatsapp: #25D366;
        }

        body {
            background-color: var(--white);
            color: #333;
            overflow-x: hidden;
        }

        /* loading screen */
        .loader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: var(--white);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            transition: opacity 0.8s ease-out, visibility 0.8s ease-out;
        }

        .loader.hidden {
            opacity: 0;
            visibility: hidden;
        }

        .logo-animation {
            width: 150px;
            height: 150px;
            background: linear-gradient(135deg, var(--primary), #012a4f);
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 30px;
            animation: pulse 1.5s infinite alternate, float 3s infinite ease-in-out;
            box-shadow: 0 10px 30px rgba(1, 57, 106, 0.4);
            position: relative;
            overflow: hidden;
        }

        .logo-animation::before {
            content: '';
            position: absolute;
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg, transparent 30%, rgba(255,255,255,0.3) 50%, transparent 70%);
            animation: shine 2s infinite;
        }

        .logo-animation i {
            font-size: 70px;
            color: var(--white);
            animation: rotate 4s linear infinite;
        }

        @keyframes shine {
            0% { transform: translateX(-100%) rotate(45deg); }
            100% { transform: translateX(100%) rotate(45deg); }
        }

        @keyframes pulse {
            0% { transform: scale(1); box-shadow: 0 10px 30px rgba(1, 57, 106, 0.3); }
            100% { transform: scale(1.05); box-shadow: 0 20px 40px rgba(1, 57, 106, 0.5); }
        }

        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-15px); }
            100% { transform: translateY(0px); }
        }

        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .loader p {
            font-size: 20px;
            color: var(--primary);
            margin-bottom: 20px;
            font-weight: 700;
        }

        .progress-bar {
            width: 250px;
            height: 6px;
            background-color: var(--gray);
            border-radius: 10px;
            overflow: hidden;
        }

        .progress-fill {
            width: 0%;
            height: 100%;
            background: linear-gradient(90deg, var(--primary), #4a90e2);
            border-radius: 10px;
            animation: loading 3s linear forwards;
        }

        @keyframes loading {
            0% { width: 0%; }
            100% { width: 100%; }
        }

        /* main website */
        .website-content {
            visibility: hidden;
            opacity: 0;
            transition: opacity 0.8s ease-out;
        }

        .website-content.visible {
            visibility: visible;
            opacity: 1;
        }

        /* whatsapp and scroll to top buttons */
        .whatsapp-btn, .scroll-top {
            position: fixed;
            width: 55px;
            height: 55px;
            border-radius: 50%;
            background-color: var(--whatsapp);
            color: white;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 28px;
            box-shadow: 0 4px 15px rgba(37, 211, 102, 0.4);
            cursor: pointer;
            transition: all 0.3s;
            z-index: 99;
            border: none;
            text-decoration: none;
        }

        .whatsapp-btn {
            bottom: 30px;
            left: 30px;
            animation: whatsappPulse 2s infinite;
        }

        .scroll-top {
            bottom: 30px;
            right: 30px;
            background-color: var(--primary);
            box-shadow: 0 4px 15px rgba(1, 57, 106, 0.4);
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s;
        }

        .scroll-top.visible {
            opacity: 1;
            visibility: visible;
        }

        .whatsapp-btn:hover, .scroll-top:hover {
            transform: scale(1.1) translateY(-5px);
        }

        @keyframes whatsappPulse {
            0% { box-shadow: 0 0 0 0 rgba(37, 211, 102, 0.7); }
            70% { box-shadow: 0 0 0 15px rgba(37, 211, 102, 0); }
            100% { box-shadow: 0 0 0 0 rgba(37, 211, 102, 0); }
        }

        /* navbar */
        .navbar {
            background-color: var(--white);
            box-shadow: 0 2px 20px rgba(1, 57, 106, 0.15);
            position: sticky;
            top: 0;
            z-index: 1000;
            padding: 0 5%;
        }

        .nav-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1400px;
            margin: 0 auto;
            min-height: 80px;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .logo-circle {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, var(--primary), #012a4f);
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            box-shadow: 0 4px 10px rgba(1, 57, 106, 0.3);
        }

        .logo-circle i {
            font-size: 26px;
            color: var(--white);
        }

        .logo h1 {
            font-size: 1.5rem;
            color: var(--primary);
            font-weight: 800;
            letter-spacing: -0.5px;
        }

        .nav-links {
            display: flex;
            list-style: none;
            gap: 40px;
        }

        .nav-links li a {
            text-decoration: none;
            color: var(--dark-gray);
            font-weight: 600;
            font-size: 1.1rem;
            position: relative;
            padding: 8px 0;
            transition: color 0.3s;
        }

        .nav-links li a:hover {
            color: var(--primary);
        }

        .nav-links li a::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 0;
            height: 3px;
            background: linear-gradient(90deg, var(--primary), #4a90e2);
            transition: width 0.3s ease;
            border-radius: 3px;
        }

        .nav-links li a:hover::after {
            width: 100%;
        }

        .nav-links li a.active {
            color: var(--primary);
            font-weight: 700;
        }

        .nav-links li a.active::after {
            width: 100%;
        }

        .menu-toggle {
            display: none;
            width: 40px;
            height: 40px;
            position: relative;
            cursor: pointer;
            z-index: 1001;
        }

        .menu-toggle i {
            font-size: 28px;
            color: var(--primary);
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            transition: opacity 0.3s;
        }

        .menu-toggle .fa-times {
            opacity: 0;
        }

        .menu-toggle.active .fa-bars {
            opacity: 0;
        }

        .menu-toggle.active .fa-times {
            opacity: 1;
        }

        /* hero slider */
       /* Hero Section - تصميم مميز */
.hero {
    position: relative;
    min-height: 100vh;
    width: 100%;
    background: linear-gradient(135deg, #f5f7fa 0%, #e8ecf2 100%);
    overflow: hidden;
    display: flex;
    align-items: center;
    padding: 120px 5% 60px;
    direction: rtl;
}

/* الخلفية المتحركة */
.hero-background {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 1;
}

.gradient-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: radial-gradient(circle at 80% 50%, rgba(1, 57, 106, 0.05) 0%, transparent 50%),
                radial-gradient(circle at 20% 30%, rgba(1, 57, 106, 0.03) 0%, transparent 50%);
}

.pattern-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%2301396a' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
}

/* الأشكال العائمة */
.floating-shapes {
    position: absolute;
    width: 100%;
    height: 100%;
    overflow: hidden;
}

.shape {
    position: absolute;
    background: var(--primary);
    border-radius: 50%;
    filter: blur(60px);
    opacity: 0.1;
    animation: float 20s infinite ease-in-out;
}

.shape-1 {
    width: 300px;
    height: 300px;
    top: -100px;
    right: -100px;
    animation-delay: 0s;
}

.shape-2 {
    width: 400px;
    height: 400px;
    bottom: -150px;
    left: -150px;
    background: #4a90e2;
    animation-delay: -5s;
}

.shape-3 {
    width: 200px;
    height: 200px;
    top: 30%;
    left: 10%;
    background: #00a86b;
    animation-delay: -10s;
}

.shape-4 {
    width: 250px;
    height: 250px;
    bottom: 20%;
    right: 15%;
    background: #ff6b6b;
    animation-delay: -15s;
}

@keyframes float {
    0%, 100% { transform: translate(0, 0) scale(1); }
    25% { transform: translate(50px, -50px) scale(1.1); }
    50% { transform: translate(100px, 0) scale(1); }
    75% { transform: translate(50px, 50px) scale(0.9); }
}

/* الحاوية الرئيسية */
.hero-container {
    position: relative;
    z-index: 2;
    max-width: 1400px;
    width: 100%;
    margin: 0 auto;
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 60px;
    align-items: center;
}

/* المحتوى */
.hero-content {
    animation: fadeInUp 1s ease-out;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* الشارة */
.hero-badge {
    display: inline-flex;
    align-items: center;
    gap: 12px;
    background: rgba(1, 57, 106, 0.1);
    backdrop-filter: blur(10px);
    padding: 10px 20px 10px 25px;
    border-radius: 50px;
    margin-bottom: 30px;
    border: 1px solid rgba(1, 57, 106, 0.2);
}

.badge-icon {
    width: 35px;
    height: 35px;
    background: var(--primary);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 16px;
}

.badge-text {
    color: var(--primary);
    font-weight: 600;
    font-size: 1rem;
}

/* العنوان */
.hero-title {
    margin-bottom: 25px;
}

.title-line {
    display: block;
    font-size: clamp(2.5rem, 8vw, 4.5rem);
    font-weight: 800;
    line-height: 1.2;
    color: #1a1a1a;
}

.gradient-text {
    background: linear-gradient(135deg, var(--primary), #4a90e2);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

/* الوصف */
.hero-description {
    font-size: clamp(1rem, 2.5vw, 1.2rem);
    line-height: 1.8;
    color: #4a4a4a;
    margin-bottom: 35px;
    max-width: 550px;
}

/* الأزرار */
.hero-buttons {
    display: flex;
    gap: 20px;
    margin-bottom: 50px;
    flex-wrap: wrap;
}

.btn {
    display: inline-flex;
    align-items: center;
    gap: 12px;
    padding: 15px 35px;
    border-radius: 50px;
    font-size: 1.1rem;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s ease;
    cursor: pointer;
    border: 2px solid transparent;
}

.btn-primary {
    background: var(--primary);
    color: white;
    box-shadow: 0 10px 20px rgba(1, 57, 106, 0.3);
}

.btn-primary:hover {
    background: #012a4f;
    transform: translateY(-5px);
    box-shadow: 0 15px 30px rgba(1, 57, 106, 0.4);
}

.btn-outline {
    background: transparent;
    color: var(--primary);
    border-color: var(--primary);
}

.btn-outline:hover {
    background: var(--primary);
    color: white;
    transform: translateY(-5px);
}

.btn i {
    font-size: 1.2rem;
    transition: transform 0.3s;
}

.btn:hover i {
    transform: translateX(-5px);
}

/* الإحصائيات */
.hero-stats {
    display: flex;
    gap: 40px;
    flex-wrap: wrap;
}

.stat-item {
    display: flex;
    flex-direction: column;
}

.stat-number {
    font-size: 2.5rem;
    font-weight: 800;
    color: var(--primary);
    line-height: 1;
    margin-bottom: 5px;
}

.stat-label {
    font-size: 1rem;
    color: #666;
    font-weight: 500;
}

/* الصورة المميزة */
.hero-image-wrapper {
    position: relative;
    animation: fadeInRight 1s ease-out;
}

@keyframes fadeInRight {
    from {
        opacity: 0;
        transform: translateX(30px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

.hero-image {
    position: relative;
    border-radius: 30px;
    overflow: hidden;
    box-shadow: 0 30px 60px rgba(1, 57, 106, 0.3);
    transform: perspective(1000px) rotateY(-5deg);
    transition: transform 0.5s;
}

.hero-image:hover {
    transform: perspective(1000px) rotateY(0deg);
}

.hero-image img {
    width: 100%;
    height: auto;
    display: block;
    transition: transform 0.5s;
}

.hero-image:hover img {
    transform: scale(1.05);
}

/* البطاقات العائمة */
.floating-card {
    position: absolute;
    background: white;
    padding: 12px 20px;
    border-radius: 15px;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
    display: flex;
    align-items: center;
    gap: 12px;
    animation: floatCard 3s infinite ease-in-out;
    backdrop-filter: blur(10px);
    background: rgba(255, 255, 255, 0.95);
    border: 1px solid rgba(255, 255, 255, 0.5);
}

.floating-card i {
    font-size: 24px;
    color: var(--primary);
}

.card-text {
    display: flex;
    flex-direction: column;
}

.card-title {
    font-weight: 700;
    color: var(--primary);
    font-size: 0.9rem;
}

.card-subtitle {
    font-size: 0.8rem;
    color: #666;
}

.card-1 {
    top: 10%;
    right: -20px;
    animation-delay: 0s;
}

.card-2 {
    bottom: 15%;
    left: -20px;
    animation-delay: 0.5s;
}

.card-3 {
    top: 50%;
    right: -30px;
    animation-delay: 1s;
}

@keyframes floatCard {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-15px); }
}

/* مؤشر التمرير */
.scroll-indicator {
    position: absolute;
    bottom: 30px;
    left: 50%;
    transform: translateX(-50%);
    z-index: 10;
}

.scroll-link {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    text-decoration: none;
    color: var(--primary);
    cursor: pointer;
}

.scroll-text {
    font-size: 0.9rem;
    font-weight: 600;
    letter-spacing: 1px;
}

.scroll-arrow {
    width: 35px;
    height: 35px;
    background: var(--primary);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    animation: bounce 2s infinite;
}

@keyframes bounce {
    0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
    40% { transform: translateY(-15px); }
    60% { transform: translateY(-7px); }
}

/* التجاوب مع الشاشات */
@media screen and (max-width: 1200px) {
    .hero-container {
        gap: 40px;
    }
    
    .floating-card {
        padding: 8px 15px;
    }
    
    .card-1 { right: -10px; }
    .card-2 { left: -10px; }
    .card-3 { right: -15px; }
}

@media screen and (max-width: 992px) {
    .hero {
        padding: 100px 5% 80px;
    }
    
    .hero-container {
        grid-template-columns: 1fr;
        text-align: center;
    }
    
    .hero-content {
        max-width: 700px;
        margin: 0 auto;
    }
    
    .hero-description {
        margin-left: auto;
        margin-right: auto;
    }
    
    .hero-buttons {
        justify-content: center;
    }
    
    .hero-stats {
        justify-content: center;
    }
    
    .hero-image-wrapper {
        max-width: 600px;
        margin: 0 auto;
    }
    
    .hero-image {
        transform: none;
    }
    
    .hero-image:hover {
        transform: scale(1.02);
    }
    
    .floating-card {
        animation: floatCard 3s infinite ease-in-out;
    }
    
    .card-1 {
        top: 5%;
        right: -10px;
    }
    
    .card-2 {
        bottom: 10%;
        left: -10px;
    }
    
    .card-3 {
        top: 45%;
        right: -15px;
    }
}

@media screen and (max-width: 768px) {
    .hero {
        padding: 90px 5% 60px;
    }
    
    .hero-badge {
        margin-bottom: 20px;
    }
    
    .hero-title {
        margin-bottom: 15px;
    }
    
    .hero-buttons {
        flex-direction: column;
        gap: 15px;
        margin-bottom: 35px;
    }
    
    .btn {
        width: 100%;
        justify-content: center;
        padding: 12px 25px;
    }
    
    .hero-stats {
        gap: 25px;
    }
    
    .stat-number {
        font-size: 2rem;
    }
    
    .stat-label {
        font-size: 0.9rem;
    }
    
    .floating-card {
        padding: 6px 12px;
    }
    
    .floating-card i {
        font-size: 18px;
    }
    
    .card-title {
        font-size: 0.8rem;
    }
    
    .card-subtitle {
        font-size: 0.7rem;
    }
    
    .card-1 { right: 0; }
    .card-2 { left: 0; }
    .card-3 { right: -5px; }
}

/* تعديل جديد للشاشات الصغيرة جداً - منتجين جنب بعض */
@media screen and (max-width: 480px) {
    /* التعديلات الموجودة لديك + هذا التعديل الجديد */
    .products-grid {
        grid-template-columns: repeat(2, 1fr) !important;
        gap: 10px;
    }
    
    .product-image {
        height: 150px;
    }
    
    .product-name {
        font-size: 0.9rem;
        margin-bottom: 5px;
    }
    
    .product-description {
        font-size: 0.75rem;
        min-height: 40px;
        margin-bottom: 8px;
    }
    
    .product-features {
        gap: 5px;
        margin-bottom: 8px;
    }
    
    .feature-tag {
        padding: 2px 6px;
        font-size: 0.65rem;
    }
    
    .product-price {
        font-size: 1rem;
        margin-bottom: 8px;
    }
    
    .product-whatsapp {
        padding: 6px;
        font-size: 0.8rem;
    }
    
    .product-whatsapp i {
        font-size: 0.9rem;
    }
    
    .product-badge {
        top: 5px;
        right: 5px;
        padding: 4px 8px;
        font-size: 0.65rem;
    }
    
    .product-brand {
        top: 5px;
        left: 5px;
        padding: 3px 8px;
        font-size: 0.65rem;
    }
}

/* للشاشات الصغيرة جداً جداً (أقل من 360px) */
@media screen and (max-width: 360px) {
    .products-grid {
        grid-template-columns: 1fr !important; /* منتج واحد في السطر */
    }
    
    .product-image {
        height: 200px;
    }
}
/* للشاشات الطويلة */
@media screen and (min-height: 900px) {
    .hero {
        min-height: 900px;
    }
}

/* تحسينات للأداء */
@media (prefers-reduced-motion: reduce) {
    * {
        animation-duration: 0.01ms !important;
        animation-iteration-count: 1 !important;
        transition-duration: 0.01ms !important;
    }
}
       /* Products Section */
.products-section {
    padding: 100px 5%;
    background: linear-gradient(135deg, #f8fafd 0%, #ffffff 100%);
    position: relative;
    overflow: hidden;
}

.container {
    max-width: 1400px;
    margin: 0 auto;
}

/* Section Header */
.section-header {
    text-align: center;
    margin-bottom: 60px;
}

.section-title {
    font-size: clamp(2rem, 5vw, 3rem);
    color: var(--primary);
    font-weight: 800;
    margin-bottom: 15px;
    position: relative;
    display: inline-block;
}

.section-title::after {
    content: '';
    position: absolute;
    bottom: -10px;
    left: 50%;
    transform: translateX(-50%);
    width: 100px;
    height: 4px;
    background: linear-gradient(90deg, var(--primary), #4a90e2);
    border-radius: 4px;
}

.section-subtitle {
    font-size: 1.1rem;
    color: #666;
    max-width: 600px;
    margin: 20px auto 0;
}

/* Categories - تصميم نصي أنيق */
/* Categories - تصميم نصي أنيق مع توسيط صحيح */
.categories-wrapper {
    margin-bottom: 50px;
    position: relative;
    width: 100%;
    overflow: hidden;
}

.categories-slider {
    display: flex;
    justify-content: center !important; /* قوة للتوسيط */
    align-items: center;
    gap: 15px;
    flex-wrap: wrap;
    margin: 0 auto;
    padding: 0;
    width: 100%;
}

.category-item {
    cursor: pointer;
    transition: transform 0.3s;
    flex-shrink: 0; /* يمنع العناصر من التقلص */
    list-style: none;
}

.category-item:hover {
    transform: translateY(-5px);
}

.category-box {
    padding: 12px 28px;
    background: white;
    border-radius: 50px;
    box-shadow: 0 5px 20px rgba(1, 57, 106, 0.08);
    transition: all 0.3s;
    border: 2px solid transparent;
    text-align: center;
    display: flex;
    align-items: center;
    justify-content: center;
}

.category-box.active {
    background: linear-gradient(135deg, var(--primary), #012a4f);
    border-color: rgba(255, 255, 255, 0.3);
    box-shadow: 0 10px 25px rgba(1, 57, 106, 0.25);
}

.category-name {
    font-size: 1.1rem;
    font-weight: 600;
    color: var(--primary);
    transition: color 0.3s;
    white-space: nowrap;
    display: block;
    line-height: 1;
}

.category-box.active .category-name {
    color: white;
}

/* للشاشات المتوسطة */
@media screen and (max-width: 992px) {
    .categories-slider {
        gap: 12px;
    }
    
    .category-box {
        padding: 10px 22px;
    }
    
    .category-name {
        font-size: 1rem;
    }
}

/* للشاشات الصغيرة - نستخدم التمرير الأفقي */
@media screen and (max-width: 768px) {
    .categories-wrapper {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: thin;
        padding-bottom: 10px;
    }
    
    .categories-slider {
        flex-wrap: nowrap;
        justify-content: flex-start !important;
        width: max-content;
        min-width: 100%;
        padding: 0 5px;
    }
    
    .categories-slider::-webkit-scrollbar {
        height: 4px;
    }
    
    .categories-slider::-webkit-scrollbar-thumb {
        background: var(--primary);
        border-radius: 10px;
    }
    
    .category-item {
        flex-shrink: 0;
    }
}

/* للشاشات الصغيرة جداً */
@media screen and (max-width: 480px) {
    .category-box {
        padding: 8px 18px;
    }
    
    .category-name {
        font-size: 0.95rem;
    }
}
/* Brands Filter */
.brands-filter {
    background: white;
    padding: 30px;
    border-radius: 20px;
    box-shadow: 0 10px 30px rgba(1, 57, 106, 0.05);
    margin-bottom: 40px;
}

.filter-title {
    color: var(--primary);
    font-size: 1.3rem;
    font-weight: 700;
    margin-bottom: 20px;
    text-align: center;
}

.brands-wrapper {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    align-items: center;
    gap: 15px;
}

.brand-btn {
    padding: 10px 25px;
    border: 2px solid var(--primary);
    background: transparent;
    color: var(--primary);
    border-radius: 50px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
}

.brand-btn:hover,
.brand-btn.active {
    background: linear-gradient(135deg, var(--primary), #012a4f);
    color: white;
    transform: translateY(-3px);
    box-shadow: 0 10px 20px rgba(1, 57, 106, 0.2);
}

/* Products Grid */
.products-container {
    min-height: 600px;
    margin-bottom: 50px;
}

.products-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 30px;
}

/* Product Card */
.product-card {
    background: white;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(1, 57, 106, 0.08);
    transition: all 0.4s;
    position: relative;
    border: 1px solid rgba(1, 57, 106, 0.05);
}

.product-card:hover {
    transform: translateY(-15px);
    box-shadow: 0 20px 50px rgba(1, 57, 106, 0.15);
}

.product-badge {
    position: absolute;
    top: 15px;
    right: 15px;
    background: linear-gradient(135deg, var(--primary), #012a4f);
    color: white;
    padding: 8px 15px;
    border-radius: 25px;
    font-size: 0.8rem;
    font-weight: 600;
    z-index: 2;
    box-shadow: 0 5px 15px rgba(1, 57, 106, 0.3);
}

.product-image {
    width: 100%;
    height: 250px;
    background: linear-gradient(135deg, #f5f7fa, #e8ecf2);
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    overflow: hidden;
}

.product-image img {
    width: 80%;
    height: 80%;
    object-fit: contain;
    transition: transform 0.5s;
}

.product-card:hover .product-image img {
    transform: scale(1.1);
}

.product-brand {
    position: absolute;
    top: 15px;
    left: 15px;
    background: white;
    color: var(--primary);
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 700;
    box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
    border: 1px solid var(--primary);
}

.product-info {
    padding: 20px;
}

.product-name {
    font-size: 1.2rem;
    font-weight: 700;
    color: var(--primary);
    margin-bottom: 10px;
}

.product-description {
    font-size: 0.9rem;
    color: #666;
    line-height: 1.6;
    margin-bottom: 15px;
    min-height: 70px;
}

.product-features {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-bottom: 15px;
}

.feature-tag {
    background: rgba(1, 57, 106, 0.05);
    color: var(--primary);
    padding: 4px 10px;
    border-radius: 15px;
    font-size: 0.8rem;
    font-weight: 500;
}

.product-price {
    font-size: 1.5rem;
    font-weight: 800;
    color: var(--primary);
    margin-bottom: 15px;
    display: flex;
    align-items: center;
    gap: 5px;
}

.product-price span {
    font-size: 0.9rem;
    font-weight: 500;
    color: #666;
}

.product-whatsapp {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    width: 100%;
    padding: 12px;
    background: linear-gradient(135deg, #25D366, #128C7E);
    color: white;
    border: none;
    border-radius: 50px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
    text-decoration: none;
}

.product-whatsapp:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 20px rgba(37, 211, 102, 0.3);
}

.product-whatsapp i {
    font-size: 1.2rem;
}

/* Load More Button */
.load-more {
    text-align: center;
    margin-top: 50px;
}

.load-more-btn {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 15px 40px;
    background: white;
    border: 2px solid var(--primary);
    color: var(--primary);
    border-radius: 50px;
    font-size: 1.1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
}

.load-more-btn:hover {
    background: linear-gradient(135deg, var(--primary), #012a4f);
    color: white;
    transform: translateY(-3px);
    box-shadow: 0 10px 30px rgba(1, 57, 106, 0.2);
}

.load-more-btn i {
    transition: transform 0.3s;
}

.load-more-btn:hover i {
    transform: translateY(5px);
}

/* No Results */
.no-results {
    grid-column: 1 / -1;
    text-align: center;
    padding: 80px 20px;
    background: white;
    border-radius: 20px;
    box-shadow: 0 10px 30px rgba(1, 57, 106, 0.05);
}

.no-results i {
    font-size: 60px;
    color: var(--primary);
    opacity: 0.3;
    margin-bottom: 20px;
}

.no-results p {
    font-size: 1.3rem;
    color: #666;
    margin-bottom: 10px;
}

.no-results span {
    color: var(--primary);
    font-weight: 700;
}

/* Responsive Design */
@media screen and (max-width: 1200px) {
    .products-grid {
        grid-template-columns: repeat(3, 1fr);
        gap: 25px;
    }
}

@media screen and (max-width: 992px) {
    .products-grid {
        grid-template-columns: repeat(3, 1fr);
        gap: 20px;
    }
    
    .categories-slider {
        gap: 12px;
    }
    
    .category-box {
        padding: 10px 22px;
    }
    
    .category-name {
        font-size: 1rem;
    }
}

@media screen and (max-width: 768px) {
    .products-section {
        padding: 80px 4%;
    }
    
    .products-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 15px;
    }
    
    .brands-wrapper {
        gap: 10px;
    }
    
    .brand-btn {
        padding: 8px 18px;
        font-size: 0.9rem;
    }
    
    .categories-slider {
        gap: 10px;
        overflow-x: auto;
        padding-bottom: 10px;
        justify-content: flex-start;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: thin;
    }
    
    .categories-slider::-webkit-scrollbar {
        height: 4px;
    }
    
    .categories-slider::-webkit-scrollbar-thumb {
        background: var(--primary);
        border-radius: 10px;
    }
    
    .category-item {
        flex-shrink: 0;
    }
}

@media screen and (max-width: 576px) {
    .products-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 12px;
    }
    
    .product-image {
        height: 180px;
    }
    
    .product-name {
        font-size: 1rem;
    }
    
    .product-description {
        font-size: 0.8rem;
        min-height: 60px;
    }
    
    .product-price {
        font-size: 1.2rem;
    }
    
    .product-whatsapp {
        padding: 10px;
        font-size: 0.9rem;
    }
    
    .category-box {
        padding: 8px 18px;
    }
    
    .category-name {
        font-size: 0.95rem;
    }
}

@media screen and (max-width: 480px) {
    .products-grid {
        grid-template-columns: 1fr;
        gap: 15px;
    }
    
    .brands-filter {
        padding: 20px;
    }
    
    .brand-btn {
        padding: 6px 15px;
        font-size: 0.8rem;
    }
    
    .category-box {
        padding: 7px 15px;
    }
    
    .category-name {
        font-size: 0.9rem;
    }
}
        /* contact section */
        .contact-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            background: linear-gradient(135deg, var(--white) 0%, #f8fafd 100%);
            border-radius: 30px;
            padding: 50px;
            box-shadow: 0 20px 40px rgba(1, 57, 106, 0.1);
            border: 1px solid rgba(1, 57, 106, 0.1);
        }

        .contact-info {
            background: linear-gradient(135deg, var(--primary), #012a4f);
            color: var(--white);
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 15px 30px rgba(1, 57, 106, 0.3);
        }

        .contact-info h3 {
            font-size: 2rem;
            margin-bottom: 30px;
            font-weight: 700;
            position: relative;
        }

        .contact-info h3::after {
            content: '';
            position: absolute;
            bottom: -10px;
            right: 0;
            width: 60px;
            height: 4px;
            background: var(--white);
            border-radius: 2px;
        }

        .info-item {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 25px;
            padding: 15px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            transition: transform 0.3s;
        }

        .info-item:hover {
            transform: translateX(-5px);
            background: rgba(255, 255, 255, 0.2);
        }

        .info-item i {
            font-size: 24px;
            width: 40px;
        }

        .info-item p {
            font-size: 1.1rem;
            font-weight: 500;
        }

        .social-links {
            display: flex;
            gap: 20px;
            margin-top: 30px;
            justify-content: center;
        }

        .social-links a {
            color: var(--white);
            font-size: 28px;
            transition: all 0.3s;
            width: 50px;
            height: 50px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .social-links a:hover {
            transform: scale(1.1) translateY(-5px);
            background: var(--white);
            color: var(--primary);
        }

        .contact-form {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            color: var(--primary);
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 1.1rem;
        }

        .form-group input,
        .form-group textarea {
            padding: 15px 20px;
            border: 2px solid var(--gray);
            border-radius: 15px;
            font-size: 1rem;
            transition: all 0.3s;
            font-family: inherit;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(1, 57, 106, 0.1);
        }

        .submit-btn {
            background: linear-gradient(135deg, var(--primary), #012a4f);
            color: var(--white);
            padding: 18px;
            border: none;
            border-radius: 15px;
            font-size: 1.2rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 10px;
            box-shadow: 0 4px 15px rgba(1, 57, 106, 0.3);
        }

        .submit-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(1, 57, 106, 0.4);
        }

        /* footer */
        footer {
            background: linear-gradient(135deg, var(--primary), #01203f);
            color: var(--white);
            text-align: center;
            padding: 60px 5% 30px;
            margin-top: 50px;
            position: relative;
        }

        .footer-content {
            max-width: 1400px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 40px;
            text-align: right;
            margin-bottom: 40px;
        }

        .footer-col h4 {
            font-size: 1.3rem;
            margin-bottom: 20px;
            position: relative;
            padding-bottom: 10px;
        }

        .footer-col h4::after {
            content: '';
            position: absolute;
            bottom: 0;
            right: 0;
            width: 50px;
            height: 3px;
            background: var(--white);
            border-radius: 2px;
        }

        .footer-col p {
            line-height: 1.8;
            color: rgba(255, 255, 255, 0.8);
            margin-bottom: 10px;
        }

        .footer-col ul {
            list-style: none;
        }

        .footer-col ul li {
            margin-bottom: 12px;
        }

        .footer-col ul li a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: color 0.3s;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .footer-col ul li a:hover {
            color: var(--white);
            transform: translateX(-5px);
        }

        .copyright {
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            padding-top: 30px;
            font-size: 0.95rem;
            color: rgba(255, 255, 255, 0.7);
        }

        /* responsive */
        @media screen and (max-width: 992px) {
            .hero .slider-container {
                height: 450px;
            }
            
            .contact-grid {
                grid-template-columns: 1fr;
            }
            
            .slide-content h2 {
                font-size: 2.5rem;
            }
        }

        @media screen and (max-width: 768px) {
            .menu-toggle {
                display: block;
            }

            .nav-links {
                position: fixed;
                top: 0;
                right: -100%;
                width: 80%;
                max-width: 400px;
                height: 100vh;
                background: linear-gradient(135deg, var(--white), #f8fafd);
                flex-direction: column;
                padding: 100px 40px 40px;
                box-shadow: -5px 0 30px rgba(0, 0, 0, 0.2);
                transition: right 0.4s cubic-bezier(0.4, 0, 0.2, 1);
                z-index: 1000;
                gap: 20px;
            }

            .nav-links.active {
                right: 0;
            }

            .nav-links li {
                width: 100%;
                opacity: 0;
                transform: translateX(30px);
                animation: slideIn 0.3s forwards;
            }

            .nav-links li:nth-child(1) { animation-delay: 0.1s; }
            .nav-links li:nth-child(2) { animation-delay: 0.2s; }
            .nav-links li:nth-child(3) { animation-delay: 0.3s; }

            @keyframes slideIn {
                to {
                    opacity: 1;
                    transform: translateX(0);
                }
            }

            .nav-links li a {
                font-size: 1.3rem;
                display: block;
                padding: 15px 0;
                border-bottom: 1px solid rgba(1, 57, 106, 0.1);
            }

            .nav-links li a::after {
                display: none;
            }

            .hero .slider-container {
                height: 350px;
            }

            .slide-content h2 {
                font-size: 1.8rem;
            }

            .slide-content p {
                font-size: 1rem;
            }

            .slider-arrow {
                width: 40px;
                height: 40px;
                font-size: 18px;
            }

            .section-title {
                font-size: 2rem;
            }

            .contact-grid {
                padding: 25px;
            }

            .whatsapp-btn, .scroll-top {
                width: 50px;
                height: 50px;
                font-size: 24px;
            }
        }

        @media screen and (max-width: 480px) {
            .logo h1 {
                font-size: 1.2rem;
            }

            .hero .slider-container {
                height: 300px;
            }

            .slide-content {
                bottom: 40px;
            }

            .slide-content h2 {
                font-size: 1.5rem;
            }

            .info-item {
                flex-direction: column;
                text-align: center;
                gap: 10px;
            }

            .products-grid {
                grid-template-columns: 1fr;
            }

            .footer-content {
                grid-template-columns: 1fr;
            }
        }
        /* loading screen */
.loader {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: var(--white);
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    z-index: 9999;
    transition: opacity 0.8s ease-out, visibility 0.8s ease-out;
}

.loader.hidden {
    opacity: 0;
    visibility: hidden;
}

.logo-animation {
    width: auto;        /* غيرناها من 150px إلى auto */
    height: auto;       /* غيرناها من 150px إلى auto */
    background: none;   /* أزلنا الخلفية */
    border-radius: 0;   /* أزلنا الدائرة */
    display: flex;
    justify-content: center;
    align-items: center;
    margin-bottom: 30px;
    animation: float 3s infinite ease-in-out;  /* أزلنا pulse و rotate */
    box-shadow: none;    /* أزلنا الظل */
    position: relative;
    overflow: visible;   /* غيرناها من hidden إلى visible */
    padding: 0;          /* أزلنا padding */
}

/* أزلنا الـ shine effect نهائياً */
.logo-animation::before {
    display: none;      /* أخفينا التأثير */
}

.loader-logo {
    width: 150px;        /* تحكم بهذا حسب حجم الصورة */
    height: auto;        /* يحافظ على التناسق */
    object-fit: contain;
    animation: none;     /* أزلنا دوران الصورة */
    filter: none;        /* أزلنا الفلتر الأبيض */
    display: block;
}

/* إذا كانت الصورة كبيرة، صغرها */
.loader-logo {
    max-width: 200px;    /* حد أقصى للعرض */
    max-height: 200px;   /* حد أقصى للارتفاع */
    width: auto;
    height: auto;
}

/* float animation فقط بدون دوران */
@keyframes float {
    0% { transform: translateY(0px); }
    50% { transform: translateY(-15px); }
    100% { transform: translateY(0px); }
}

.loader p {
    font-size: 20px;
    color: var(--primary);
    margin-bottom: 20px;
    font-weight: 700;
}

.progress-bar {
    width: 250px;
    height: 6px;
    background-color: var(--gray);
    border-radius: 10px;
    overflow: hidden;
}

.progress-fill {
    width: 0%;
    height: 100%;
    background: linear-gradient(90deg, var(--primary), #4a90e2);
    border-radius: 10px;
    animation: loading 3s linear forwards;
}

@keyframes loading {
    0% { width: 0%; }
    100% { width: 100%; }
}
/* تعديل شكل الشعار في الناف بار */
.logo {
    display: flex;
    align-items: center;
    gap: 12px;
}

.logo-circle {
    width: 60px;           /* حجم الدائرة */
    height: 60px;          /* حجم الدائرة */
    background: linear-gradient(135deg, var(--primary), #012a4f);  /* خلفية دائرية */
    border-radius: 50%;    /* تجعل الخلفية دائرية */
    display: flex;
    justify-content: center;
    align-items: center;
    box-shadow: 0 4px 10px rgba(1, 57, 106, 0.3);
    overflow: hidden;      /* مهم: يخفي أي جزء زائد من الصورة */
    padding: 8px;          /* مسافة بين الصورة وحافة الدائرة */
}

.nav-logo {
    width: 100%;           /* يملأ المساحة المتاحة */
    height: 100%;          /* يملأ المساحة المتاحة */
    object-fit: contain;   /* يحافظ على تناسق الصورة */
    display: block;
    filter: none;          /* بدون فلتر */
    background: transparent;
}

/* إذا كانت الصورة تحتاج إلى تكبير أو تصغير */
.nav-logo {
    width: 80%;            /* يمكنك تعديل النسبة */
    height: 80%;
    object-fit: cover;     /* إذا تريد تغطية الدائرة بالكامل */
}

/* للهواتف الأصغر */
@media screen and (max-width: 768px) {
    .logo-circle {
        width: 50px;
        height: 50px;
        padding: 6px;
    }
    
    .logo h1 {
        font-size: 1.2rem;
    }
}

@media screen and (max-width: 480px) {
    .logo-circle {
        width: 45px;
        height: 45px;
        padding: 5px;
    }
    
    .logo h1 {
        font-size: 1rem;
    }
}
/* contact section redesign */
.contact-grid {
    display: grid;
    grid-template-columns: 2fr 1.5fr;
    gap: 30px;
    margin-top: 40px;
}

/* contact cards */
.contact-cards {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
}

.contact-card {
    background: var(--white);
    border-radius: 20px;
    padding: 25px 20px;
    box-shadow: 0 10px 30px rgba(1, 57, 106, 0.08);
    transition: all 0.3s;
    border: 1px solid rgba(1, 57, 106, 0.05);
    display: flex;
    align-items: flex-start;
    gap: 15px;
}

.contact-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 40px rgba(1, 57, 106, 0.15);
    border-color: var(--primary);
}

.card-icon {
    width: 50px;
    height: 50px;
    min-width: 50px;
    background: linear-gradient(135deg, rgba(1, 57, 106, 0.1), rgba(1, 57, 106, 0.05));
    border-radius: 15px;
    display: flex;
    justify-content: center;
    align-items: center;
    color: var(--primary);
    font-size: 22px;
    transition: all 0.3s;
}

.contact-card:hover .card-icon {
    background: var(--primary);
    color: white;
}

.card-content {
    flex: 1;
}

.card-content h4 {
    color: var(--primary);
    font-size: 1.1rem;
    font-weight: 700;
    margin-bottom: 8px;
}

.card-content p {
    color: #555;
    font-size: 0.95rem;
    line-height: 1.6;
    margin-bottom: 5px;
}

.friday {
    color: var(--primary);
    font-weight: 600;
    margin-top: 5px;
    padding-top: 5px;
    border-top: 1px dashed rgba(1, 57, 106, 0.2);
}

.whatsapp-link {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: #25D366;
    color: white;
    padding: 8px 15px;
    border-radius: 25px;
    font-size: 0.9rem;
    font-weight: 600;
    text-decoration: none;
    margin-top: 10px;
    transition: all 0.3s;
}

.whatsapp-link:hover {
    background: #128C7E;
    transform: scale(1.05);
}

/* map section */
.contact-map {
    background: var(--white);
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(1, 57, 106, 0.08);
    border: 1px solid rgba(1, 57, 106, 0.05);
}

.map-container {
    width: 100%;
    height: 250px;
    overflow: hidden;
}

.map-container iframe {
    width: 100%;
    height: 100%;
    transition: transform 0.3s;
}

.map-container:hover iframe {
    transform: scale(1.05);
}

/* social links in map */
.social-links {
    padding: 20px;
    text-align: center;
}

.social-links h4 {
    color: var(--primary);
    font-size: 1.1rem;
    margin-bottom: 15px;
    font-weight: 600;
}

.social-icons {
    display: flex;
    justify-content: center;
    gap: 12px;
    flex-wrap: wrap;
}

.social-icon {
    width: 45px;
    height: 45px;
    border-radius: 12px;
    display: flex;
    justify-content: center;
    align-items: center;
    color: white;
    font-size: 20px;
    transition: all 0.3s;
    text-decoration: none;
}

.social-icon:hover {
    transform: translateY(-5px) rotate(5deg);
}

.social-icon.facebook { background: #1877F2; }
.social-icon.instagram { background: #E4405F; }
.social-icon.whatsapp { background: #25D366; }
.social-icon.telegram { background: #0088cc; }
.social-icon.tiktok { background: #000000; }

/* responsive */
@media screen and (max-width: 992px) {
    .contact-grid {
        grid-template-columns: 1fr;
    }
    
    .contact-cards {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media screen and (max-width: 768px) {
    .contact-cards {
        grid-template-columns: 1fr;
    }
    
    .contact-card {
        padding: 20px;
    }
    
    .card-icon {
        width: 45px;
        height: 45px;
        min-width: 45px;
        font-size: 20px;
    }
}

@media screen and (max-width: 480px) {
    .map-container {
        height: 200px;
    }
    
    .social-icon {
        width: 40px;
        height: 40px;
        font-size: 18px;
    }
}
.card-content p {
    direction: ltr;
    text-align: left;
}
.phone-number {
    direction: ltr;
    unicode-bidi: embed;
}
/* تنسيق شاشة التحميل */
.loader {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: var(--white);
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    z-index: 9999;
    transition: opacity 0.8s ease-out, visibility 0.8s ease-out;
}

.loader.hidden {
    opacity: 0;
    visibility: hidden;
}

.logo-animation {
    width: auto;
    height: auto;
    background: none;
    border-radius: 0;
    display: flex;
    justify-content: center;
    align-items: center;
    margin-bottom: 30px;
    animation: float 3s infinite ease-in-out;
    box-shadow: none;
    position: relative;
    overflow: visible;
    padding: 0;
}

.logo-animation::before {
    display: none;
}

/* الصورة الدائرية */
.loader-logo {
    width: 180px;        /* حجم الصورة */
    height: 180px;       /* نفس العرض عشان تكون دائرية */
    object-fit: cover;   /* تغطي المساحة كاملة */
    animation: none;     
    filter: none;        
    display: block;
    border-radius: 50%;  /* تجعل الصورة دائرية */
    border: 4px solid var(--primary);  /* إطار باللون الأزرق */
    box-shadow: 0 10px 30px rgba(1, 57, 106, 0.3);  /* ظل خفيف */
    transition: all 0.3s;
}

/* إذا حابة يكون في bordar بيضاء */
.loader-logo.white-border {
    border: 4px solid white;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
}

/* إذا حابة يكون في borda أنحف أو أسمك */
.loader-logo.thin-border {
    border: 2px solid var(--primary);
}

.loader-logo.thick-border {
    border: 6px solid var(--primary);
}

/* تأثير float للصورة */
@keyframes float {
    0% { transform: translateY(0px); }
    50% { transform: translateY(-15px); }
    100% { transform: translateY(0px); }
}

/* للشاشات الصغيرة */
@media screen and (max-width: 768px) {
    .loader-logo {
        width: 150px;
        height: 150px;
        border-width: 3px;
    }
}

@media screen and (max-width: 480px) {
    .loader-logo {
        width: 120px;
        height: 120px;
        border-width: 3px;
    }
}

.loader p {
    font-size: 20px;
    color: var(--primary);
    margin-bottom: 20px;
    font-weight: 700;
    text-align: center;
    padding: 0 20px;
}

.progress-bar {
    width: 250px;
    height: 6px;
    background-color: var(--gray);
    border-radius: 10px;
    overflow: hidden;
}

.progress-fill {
    width: 0%;
    height: 100%;
    background: linear-gradient(90deg, var(--primary), #4a90e2);
    border-radius: 10px;
    animation: loading 3s linear forwards;
}

@keyframes loading {
    0% { width: 0%; }
    100% { width: 100%; }
}/* إطار ذهبي */
.loader-logo.gold-border {
    border: 4px solid #FFD700;
}

/* إطار فضي */
.loader-logo.silver-border {
    border: 4px solid #C0C0C0;
}

/* بدون إطار */
.loader-logo.no-border {
    border: none;
}
.scroll-top {
    position: fixed;
    bottom: 30px;
    right: 30px;
    width: 55px;
    height: 55px;
    border-radius: 50%;
    background-color: var(--primary);
    color: white;
    display: flex;
    justify-content: center;
    align-items: center;
    font-size: 28px;
    box-shadow: 0 4px 15px rgba(1, 57, 106, 0.4);
    cursor: pointer;
    transition: all 0.3s;
    z-index: 99;
    border: none;
    opacity: 0;
    visibility: hidden;
}

.scroll-top.visible {
    opacity: 1;
    visibility: visible;
}

.scroll-top:hover {
    transform: scale(1.1) translateY(-5px);
}.scroll-top {
    position: fixed;
    bottom: 30px;
    right: 30px;
    width: 55px;
    height: 55px;
    border-radius: 50%;
    background-color: var(--primary);
    color: white;
    display: flex;
    justify-content: center;
    align-items: center;
    font-size: 28px;
    box-shadow: 0 4px 15px rgba(1, 57, 106, 0.4);
    cursor: pointer;
    transition: all 0.3s;
    z-index: 9999;
    border: none;
    opacity: 0;
    visibility: hidden;
}

.scroll-top.visible {
    opacity: 1;
    visibility: visible;
}

.scroll-top:hover {
    transform: scale(1.1) translateY(-5px);
}
    </style>
</head>
<body>

    
    <!-- loading screen -->
    <div class="loader" id="loader">
        <div class="logo-animation">
            <img src="img/1.jpeg" alt="معرض رأفت البهنسي" class="loader-logo">
        </div>
        <p>معرض رأفت البهنسي للأجهزة المنزلية</p>
        <div class="progress-bar">
            <div class="progress-fill"></div>
        </div>
    </div>

    <!-- main content -->
    <div class="website-content" id="websiteContent">
        <!-- whatsapp button -->
        <a href="https://wa.me/962799723795" class="whatsapp-btn" target="_blank">
            <i class="fab fa-whatsapp"></i>
        </a>
        
        <!-- scroll to top button -->
        <div class="scroll-top" id="scrollTop">
            <i class="fas fa-arrow-up"></i>
        </div>

        <!-- navbar -->
        <nav class="navbar">
            <div class="nav-container">
                <div class="logo">
                    <div class="logo-circle">
                        <img src="img/2.png" alt="معرض رأفت البهنسي" class="nav-logo">
                    </div>
                    <h1>معرض رأفت البهنسي</h1>
                </div>
                
                <div class="menu-toggle" id="menuToggle">
                    <i class="fas fa-bars"></i>
                    <i class="fas fa-times"></i>
                </div>
                
                <ul class="nav-links" id="navLinks">
                    <li><a href="#home" class="active">الرئيسية</a></li>
                    <li><a href="#products">منتجاتنا</a></li>
                    <li><a href="#contact">تواصل معنا</a></li>
                </ul>
            </div>
        </nav>

        <!-- Hero Section -->
        <section class="hero" id="home">
            <div class="hero-background">
                <div class="gradient-overlay"></div>
                <div class="pattern-overlay"></div>
                <div class="floating-shapes">
                    <div class="shape shape-1"></div>
                    <div class="shape shape-2"></div>
                </div>
            </div>

            <div class="hero-container">
                <div class="hero-content">
                    <h1 class="hero-title">
                        <span class="title-line">معرض رأفت البهنسي</span>
                        <span class="title-line gradient-text">للأجهزة المنزلية</span>
                    </h1>

                    <p class="hero-description">
                        أفضل الأجهزة المنزلية من أشهر الماركات العالمية بأسعار تنافسية 
                        تسوق الآن واستمتع بتجربة شراء فريدة.
                    </p>

                    <div class="hero-buttons">
                        <a href="#products" class="btn btn-primary">
                            <span>تسوق الآن</span>
                            <i class="fas fa-arrow-left"></i>
                        </a>
                        <a href="#contact" class="btn btn-outline">
                            <i class="fas fa-phone-alt"></i>
                            <span>تواصل معنا</span>
                        </a>
                    </div>
                </div>

                <div class="hero-image-wrapper">
                    <div class="hero-image">
                        <img src="img/3.jpeg" alt="أجهزة منزلية">
                    </div>
                </div>
            </div>

            <div class="scroll-indicator">
                <a href="#products" class="scroll-link">
                    <span class="scroll-text">اكتشف المزيد</span>
                    <div class="scroll-arrow">
                        <i class="fas fa-chevron-down"></i>
                    </div>
                </a>
            </div>
        </section>

        <!-- Products Section -->
        <section class="products-section" id="products">
            <div class="container">
                <div class="section-header">
                    <h2 class="section-title">منتجاتنا</h2>
                    <p class="section-subtitle">تصفح أحدث الأجهزة المنزلية من أفضل الماركات العالمية</p>
                </div>

                <!-- Categories -->
                <div class="categories-wrapper">
                    <div class="categories-slider" id="categoriesSlider">
                        <div class="category-item" data-category="all">
                            <div class="category-box active">
                                <span class="category-name">الكل</span>
                            </div>
                        </div>
                        <?php foreach ($categories as $category): ?>
                        <div class="category-item" data-category="<?php echo $category['slug']; ?>">
                            <div class="category-box">
                                <span class="category-name"><?php echo $category['name_ar']; ?></span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Brands Filter -->
                <div class="brands-filter">
                    <h3 class="filter-title">الماركات</h3>
                    <div class="brands-wrapper" id="brandsWrapper">
                        <button class="brand-btn active" data-brand="all">الكل</button>
                        <?php foreach ($brands as $brand): ?>
                        <button class="brand-btn" data-brand="<?php echo $brand['slug']; ?>"><?php echo $brand['name_ar']; ?></button>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Products Grid -->
                <div class="products-container">
                    <div class="products-grid" id="productsGrid">
                        <?php foreach ($products as $product): 
                            $features = json_decode($product['features'], true) ?: [];
                        ?>
                        <div class="product-card" data-category="<?php echo $product['category_slug']; ?>" data-brand="<?php echo $product['brand_slug']; ?>">
                            <?php if ($product['is_bestseller']): ?>
                            <span class="product-badge">الأكثر مبيعاً</span>
                            <?php elseif ($product['is_new']): ?>
                            <span class="product-badge">جديد</span>
                            <?php endif; ?>
                            
                            <div class="product-image">
                                <?php if ($product['image'] && file_exists("admin/uploads/products/" . $product['image'])): ?>
                                <img src="admin/uploads/products/<?php echo $product['image']; ?>" alt="<?php echo $product['name_ar']; ?>">
                                <?php else: ?>
                                <img src="https://via.placeholder.com/300x300?text=<?php echo urlencode($product['name_ar']); ?>" alt="<?php echo $product['name_ar']; ?>">
                                <?php endif; ?>
                                <span class="product-brand"><?php echo $product['brand_name']; ?></span>
                            </div>
                            
                            <div class="product-info">
                                <h3 class="product-name"><?php echo $product['name_ar']; ?></h3>
                                <p class="product-description"><?php echo $product['description_ar'] ?: ''; ?></p>
                                
                                <?php if (!empty($features)): ?>
                                <div class="product-features">
                                    <?php foreach ($features as $feature): ?>
                                    <?php if ($feature): ?>
                                    <span class="feature-tag"><?php echo $feature; ?></span>
                                    <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                                <?php endif; ?>
                                
                                <div class="product-price">
                                    <?php if ($product['price']): ?>
                                        <?php echo number_format($product['price']); ?> ل.س
                                        <?php if ($product['old_price']): ?>
                                        <span><del><?php echo number_format($product['old_price']); ?> ل.س</del></span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        اتصل للسعر
                                    <?php endif; ?>
                                </div>
                                
                               <button class="product-whatsapp" onclick="openWhatsApp('<?php echo $product['id']; ?>')">
    <i class="fab fa-whatsapp"></i> تواصل معنا
</button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <?php if (empty($products)): ?>
                    <div class="no-results" id="noResults">
                        <i class="fas fa-box-open"></i>
                        <p>لا توجد منتجات حالياً</p>
                        <span>سيتم إضافة منتجات قريباً</span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <!-- Contact Section -->
        <section class="section" id="contact">
            <div class="container">
                <div class="section-header">
                    <h2 class="section-title">تواصل معنا</h2>
                </div>
            </div>
            
            <div class="contact-grid">
                <div class="contact-cards">
                    <div class="contact-card">
                        <div class="card-icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <div class="card-content">
                            <h4>العنوان</h4>
                            <p>سوريا - دمشق - المتحلق الجنوبي <br>عند تحويلة المليحة</p>
                        </div>
                    </div>

                    <div class="contact-card">
                        <div class="card-icon">
                            <i class="fas fa-phone-alt"></i>
                        </div>
                        <div class="card-content">
                            <h4>الهاتف</h4>
                            <p dir="ltr">+962 79 972 3795</p>
                        </div>
                    </div>

                    <div class="contact-card">
                        <div class="card-icon">
                            <i class="fab fa-whatsapp"></i>
                        </div>
                        <div class="card-content">
                            <h4>واتساب</h4>
                            <p dir="ltr">+962 79 972 3795</p>
                            <a href="https://wa.me/962799723795" class="whatsapp-link" target="_blank">
                                <i class="fab fa-whatsapp"></i> راسلنا الآن
                            </a>
                        </div>
                    </div>

                    <div class="contact-card">
                        <div class="card-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="card-content">
                            <h4>ساعات العمل</h4>
                            <p>السبت - الخميس: 9:00 صباحاً - 9:00 مساءً</p>
                            <p class="friday">الجمعة: 2:00 مساءً - 9:00 مساءً</p>
                        </div>
                    </div>
                </div>

                <div class="contact-map">
                    <div class="map-container">
                        <iframe 
                            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d13319.857012916947!2d36.2973!3d33.5119!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x1518e7c5b3f7c0b1%3A0x5e5f5f5f5f5f5f!2z2YXYs9iq2LHYjCDZhNiq2LHYp9iz!5e0!3m2!1sar!2s!4v1234567890" 
                            style="border:0;" 
                            allowfullscreen="" 
                            loading="lazy">
                        </iframe>
                    </div>
                    
                    <div class="social-links">
                        <div class="social-icons">
                            <a href="https://www.facebook.com/profile.php?id=61580948429682" class="social-icon facebook" target="_blank"><i class="fab fa-facebook-f"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer>
            <div class="footer-content">
                <div class="footer-col">
                    <h4>معرض رأفت البهنسي</h4>
                    <p>نقدم أفضل المنتجات بأفضل الأسعار و الماركات.</p>
                </div>
                
                <div class="footer-col">
                    <h4>روابط سريعة</h4>
                    <ul>
                        <li><a href="#home"><i class="fas fa-chevron-left"></i> الرئيسية</a></li>
                        <li><a href="#products"><i class="fas fa-chevron-left"></i> منتجاتنا</a></li>
                        <li><a href="#contact"><i class="fas fa-chevron-left"></i> تواصل معنا</a></li>
                    </ul>
                </div>
                
                <div class="footer-col">
                    <h4>تواصل معنا</h4>
                    <ul>
                        <li><i class="fas fa-phone"></i> <span dir="ltr">+962 79 972 3795</span></li>
                        <li><i class="fab fa-whatsapp"></i> <span dir="ltr">+962 79 972 3795</span></li>
                        <li><i class="fas fa-map-marker-alt"></i> دمشق - سوريا</li>
                    </ul>
                </div>
            </div>
            
            <div class="copyright">
                <p>جميع الحقوق محفوظة &copy; 2024 معرض رأفت البهنسي للأجهزة المنزلية</p>
            </div>
        </footer>
    </div>

    <script>
    // ========== شاشة التحميل ==========
    setTimeout(() => {
        document.getElementById('loader').classList.add('hidden');
        document.getElementById('websiteContent').classList.add('visible');
    }, 3000);

    // ========== القائمة الجانبية للموبايل ==========
    const menuToggle = document.getElementById('menuToggle');
    const navLinks = document.getElementById('navLinks');
    
    if (menuToggle && navLinks) {
        menuToggle.addEventListener('click', function() {
            this.classList.toggle('active');
            navLinks.classList.toggle('active');
            document.body.style.overflow = navLinks.classList.contains('active') ? 'hidden' : '';
        });
        
        document.querySelectorAll('.nav-links a').forEach(link => {
            link.addEventListener('click', function() {
                menuToggle.classList.remove('active');
                navLinks.classList.remove('active');
                document.body.style.overflow = '';
            });
        });
    }

    // ========== التنعيم عند النقر على الروابط ==========
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href');
            if (targetId === '#') return;
            
            const targetElement = document.querySelector(targetId);
            if (targetElement) {
                targetElement.scrollIntoView({
                    behavior: 'smooth'
                });
            }
        });
    });

    // ========== الفلترة ==========
    let currentCategory = 'all';
    let currentBrand = 'all';
    
    const categoryItems = document.querySelectorAll('.category-item');
    const brandBtns = document.querySelectorAll('.brand-btn');
    const productCards = document.querySelectorAll('.product-card');

    function filterProducts() {
        productCards.forEach(card => {
            const category = card.dataset.category;
            const brand = card.dataset.brand;
            
            const categoryMatch = currentCategory === 'all' || category === currentCategory;
            const brandMatch = currentBrand === 'all' || brand === currentBrand;
            
            card.style.display = categoryMatch && brandMatch ? 'block' : 'none';
        });
    }

    // Category click
    categoryItems.forEach(item => {
        item.addEventListener('click', function() {
            categoryItems.forEach(cat => cat.querySelector('.category-box').classList.remove('active'));
            this.querySelector('.category-box').classList.add('active');
            
            currentCategory = this.dataset.category;
            currentBrand = 'all';
            
            brandBtns.forEach(btn => {
                btn.classList.remove('active');
                if (btn.dataset.brand === 'all') btn.classList.add('active');
            });
            
            filterProducts();
        });
    });

    // Brand click
    brandBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            brandBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            currentBrand = this.dataset.brand;
            filterProducts();
        });
    });

// بيانات المنتجات (أنشئها مرة واحدة)
const productsData = {
    <?php foreach ($products as $product): ?>
    "<?php echo $product['id']; ?>": {
        name: "<?php echo addslashes($product['name_ar']); ?>",
        price: "<?php echo $product['price'] ? number_format($product['price']) : ''; ?>",
        description: "<?php echo addslashes($product['description_ar']); ?>",
        brand: "<?php echo addslashes($product['brand_name']); ?>",
        features: <?php echo json_encode($features); ?>
    },
    <?php endforeach; ?>
};

// دالة الواتساب
function openWhatsApp(productId) {
    const product = productsData[productId];
    if (!product) return;
    
    // تجهيز نص المميزات
    let featuresText = '';
    if (product.features && product.features.length > 0) {
        featuresText = '\nالمميزات:\n';
        product.features.forEach(feature => {
            if (feature) {
                featuresText += `- ${feature}\n`;
            }
        });
    }
    
    // تجهيز السعر
    const priceText = product.price ? `${product.price} ل.س` : 'قابل للتفاوض';
    
    // إنشاء الرسالة
    const message = `السلام عليكم

استفسار عن منتج:

المنتج: ${product.name}
الماركة: ${product.brand}
السعر: ${priceText}
الوصف: ${product.description || 'لا يوجد وصف'}${featuresText}

الرجاء الرد مع التفاصيل`;
    
    // فتح الواتساب
    window.open(`https://wa.me/962799723795?text=${encodeURIComponent(message)}`, '_blank');
}
    // ========== زر العودة للأعلى ==========
    const scrollBtn = document.getElementById('scrollTop');
    
    window.addEventListener('scroll', function() {
        if (window.scrollY > 500) {
            scrollBtn.classList.add('visible');
        } else {
            scrollBtn.classList.remove('visible');
        }
    });
    
    scrollBtn.addEventListener('click', function() {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
    </script>
</body>
</html>