// قائمة الجانبية للموبايل
document.addEventListener('DOMContentLoaded', function() {
    // تفعيل الرابط النشط في القائمة
    const currentPage = window.location.pathname.split('/').pop();
    const menuLinks = document.querySelectorAll('.sidebar-menu a');
    
    menuLinks.forEach(link => {
        const linkPage = link.getAttribute('href');
        if (linkPage === currentPage) {
            link.closest('li').classList.add('active');
        }
    });
    
    // معاينة الصور قبل الرفع
    const fileInputs = document.querySelectorAll('input[type="file"]');
    fileInputs.forEach(input => {
        input.addEventListener('change', function(e) {
            const preview = this.closest('.file-input').querySelector('.file-preview');
            if (preview && this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.innerHTML = `<img src="${e.target.result}" style="max-width: 100%; max-height: 200px; border-radius: 8px;">`;
                }
                reader.readAsDataURL(this.files[0]);
            }
        });
    });
    
    // تأكيد الحذف
    const deleteButtons = document.querySelectorAll('.delete-confirm');
    deleteButtons.forEach(btn => {
        btn.addEventListener('click', function(e) {
            if (!confirm('هل أنت متأكد من حذف هذا العنصر؟')) {
                e.preventDefault();
            }
        });
    });
    
    // إظهار/إخفاء حقل الضمان
    const warrantyCheckbox = document.querySelector('input[name="has_warranty"]');
    const warrantyField = document.getElementById('warrantyField');
    
    if (warrantyCheckbox && warrantyField) {
        warrantyCheckbox.addEventListener('change', function() {
            warrantyField.style.display = this.checked ? 'block' : 'none';
        });
    }
    
    // فلترة الجدول
    const filterInput = document.getElementById('tableFilter');
    if (filterInput) {
        filterInput.addEventListener('keyup', function() {
            const filterValue = this.value.toLowerCase();
            const table = document.querySelector('.table');
            const rows = table.querySelectorAll('tbody tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(filterValue) ? '' : 'none';
            });
        });
    }
});

// toggle القائمة الجانبية للموبايل
function toggleSidebar() {
    document.querySelector('.sidebar').classList.toggle('active');
}