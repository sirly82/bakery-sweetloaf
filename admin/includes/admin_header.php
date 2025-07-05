<?php
// admin/includes/admin_header.php

// Pastikan sesi dimulai di halaman yang memanggil header ini,
// atau di admin_auth.php yang di-include sebelum header ini.
// Kita asumsikan session_start() sudah dipanggil oleh dashboard.php atau admin_auth.php.

// Ambil username admin dari sesi, gunakan default jika tidak diset (walaupun harusnya diset oleh admin_auth.php)
$current_admin_username = $_SESSION['admin_username'] ?? 'Admin';
?>
<header class="admin-header">
    <div class="admin-header-left">
        <button class="menu-toggle" id="menuToggle" aria-label="Toggle Sidebar">
            <i class="fas fa-bars"></i> </button>
        <div class="logo">
            <img src="../assets/logo-home.png" alt="SweetLoaf Admin Logo">
        </div>
    </div>
    <div class="admin-header-right">
        <span class="welcome-message">Halo, <strong><?php echo htmlspecialchars($current_admin_username); ?></strong>!</span>
        <a href="../logout.php" class="btn-logout">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>
</header>