<footer class="admin-footer">
    <p>&copy; <?php echo date("Y"); ?> SweetLoaf Bakery Admin. All Rights Reserved.</p>
</footer>

<script src="admin.js"></script>

<script>
    // --- JavaScript untuk Sidebar Toggle ---
    // Kode ini bisa dipindahkan ke admin.js untuk kebersihan
    document.addEventListener('DOMContentLoaded', function() {
        const menuToggle = document.getElementById('menuToggle');
        const adminSidebar = document.getElementById('adminSidebar');
        const adminMainContent = document.querySelector('.admin-main-content'); // Konten utama

        if (menuToggle && adminSidebar && adminMainContent) {
            menuToggle.addEventListener('click', () => {
                adminSidebar.classList.toggle('active');
                adminMainContent.classList.toggle('sidebar-active'); // Tambahkan kelas ke main content
            });

            // Opsional: Klik di luar sidebar untuk menutupnya
            // adminMainContent.addEventListener('click', () => {
            //     if (adminSidebar.classList.contains('active')) {
            //         adminSidebar.classList.remove('active');
            //         adminMainContent.classList.remove('sidebar-active');
            //     }
            // });
        }
    });
</script>