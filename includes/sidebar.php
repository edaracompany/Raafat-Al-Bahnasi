<?php
// sidebar.php - القائمة الجانبية
$current_page = basename($_SERVER['PHP_SELF']);
?>
<div class="sidebar">
    <div class="sidebar-header">
        <div class="logo-circle">
            <i class="fas fa-store"></i>
        </div>
        <h3>معرض رأفت البهنسي</h3>
        <p>لوحة التحكم</p>
    </div>
    
    <div class="sidebar-menu">
        <ul>
            <li class="<?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>">
                <a href="dashboard.php">
                    <i class="fas fa-home"></i>
                    <span>الرئيسية</span>
                </a>
            </li>
            <li class="<?php echo $current_page == 'products.php' ? 'active' : ''; ?>">
                <a href="products.php">
                    <i class="fas fa-box"></i>
                    <span>المنتجات</span>
                </a>
            </li>
            <li class="<?php echo $current_page == 'product-add.php' ? 'active' : ''; ?>">
                <a href="product-add.php">
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