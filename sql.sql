-- ==============================================
-- إنشاء قاعدة البيانات
-- ==============================================
CREATE DATABASE IF NOT EXISTS rafat_store;
USE rafat_store;

-- ==============================================
-- جدول الأقسام (categories)
-- ==============================================
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name_ar VARCHAR(100) NOT NULL,
    name_en VARCHAR(100) NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL,
    icon VARCHAR(50),
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ==============================================
-- جدول الماركات (brands)
-- ==============================================
CREATE TABLE brands (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name_ar VARCHAR(100) NOT NULL,
    name_en VARCHAR(100) NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL,
    logo VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ==============================================
-- جدول المنتجات (products)
-- ==============================================
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    brand_id INT NOT NULL,
    name_ar VARCHAR(200) NOT NULL,
    name_en VARCHAR(200),
    description_ar TEXT,
    description_en TEXT,
    price DECIMAL(10,2),
    old_price DECIMAL(10,2),
    image VARCHAR(255),
    additional_images TEXT,
    features TEXT,
    is_bestseller BOOLEAN DEFAULT FALSE,
    is_new BOOLEAN DEFAULT TRUE,
    has_warranty BOOLEAN DEFAULT TRUE,
    warranty_years INT DEFAULT 2,
    stock INT DEFAULT 0,
    sort_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
    FOREIGN KEY (brand_id) REFERENCES brands(id) ON DELETE CASCADE
);

-- ==============================================
-- جدول المستخدمين (للإدارة)
-- ==============================================
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    full_name VARCHAR(100),
    role ENUM('admin', 'editor') DEFAULT 'editor',
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ==============================================
-- إدخال البيانات الأولية
-- ==============================================

-- إفراغ الجداول أولاً (إذا كان فيها بيانات)
DELETE FROM products;
DELETE FROM categories;
DELETE FROM brands;
DELETE FROM users;

-- إعادة تعيين الترقيم التلقائي (Auto Increment)
ALTER TABLE categories AUTO_INCREMENT = 1;
ALTER TABLE brands AUTO_INCREMENT = 1;
ALTER TABLE products AUTO_INCREMENT = 1;
ALTER TABLE users AUTO_INCREMENT = 1;

-- إدخال الأقسام
INSERT INTO categories (name_ar, name_en, slug, sort_order) VALUES
('برادات', 'Refrigerators', 'refrigerators', 1),
('غسالات', 'Washing Machines', 'washing-machines', 2),
('دفايات', 'Heaters', 'heaters', 3),
('أفران', 'Ovens', 'ovens', 4),
('مراوح', 'Fans', 'fans', 5),
('ميكرويف', 'Microwaves', 'microwaves', 6),
('مكانس', 'Vacuums', 'vacuums', 7);

-- إدخال الماركات
INSERT INTO brands (name_ar, name_en, slug) VALUES
('LG', 'LG', 'lg'),
('سامسونج', 'Samsung', 'samsung'),
('شارب', 'Sharp', 'sharp'),
('تورنيدو', 'Tornado', 'tornado'),
('بيكو', 'Beko', 'beko'),
('أريستون', 'Ariston', 'ariston'),
('إنديسيت', 'Indesit', 'indesit'),
('باناسونيك', 'Panasonic', 'panasonic');

-- إدخال المستخدم (كلمة المرور: admin123)
INSERT INTO users (username, password, email, full_name, role) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@rafat-store.com', 'مدير النظام', 'admin');

-- ==============================================
-- إضافة بعض المنتجات التجريبية (اختياري)
-- ==============================================

-- إضافة منتج تجريبي: غسالة LG
INSERT INTO products (category_id, brand_id, name_ar, description_ar, price, image, features, is_bestseller, is_new, has_warranty, warranty_years, is_active) 
VALUES 
(2, 1, 'غسالة LG 12 كغم', 'غسالة أوتوماتيك مع تجفيف 100% وتقنية Steam', 450000, 'lg-washer.jpg', '["تجفيف كامل", "بخار", "إنفرتر"]', TRUE, TRUE, TRUE, 2, TRUE);

-- ==============================================
-- عرض البيانات للتأكد
-- ==============================================

SELECT 'الأقسام' AS 'الجدول', COUNT(*) AS 'العدد' FROM categories
UNION ALL
SELECT 'الماركات', COUNT(*) FROM brands
UNION ALL
SELECT 'المستخدمين', COUNT(*) FROM users
UNION ALL
SELECT 'المنتجات', COUNT(*) FROM products;

-- عرض بيانات المستخدم
SELECT id, username, email, full_name, role FROM users;