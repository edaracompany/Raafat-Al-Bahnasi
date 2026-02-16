<?php
// header.php - الشريط العلوي
?>
<div class="header">
    <div class="header-left">
        <div class="menu-toggle" onclick="toggleSidebar()">
            <i class="fas fa-bars"></i>
        </div>
    </div>
    
    <div class="header-right">
        <div class="user-info">
            <div class="user-avatar">
                <i class="fas fa-user"></i>
            </div>
            <div class="user-details">
                <div class="user-name"><?php echo $_SESSION['user_name'] ?? 'مدير'; ?></div>
                <div class="user-role"><?php echo $_SESSION['user_role'] ?? 'admin'; ?></div>
            </div>
        </div>
    </div>
</div>

<script>
function toggleSidebar() {
    document.querySelector('.sidebar').classList.toggle('active');
}
</script>