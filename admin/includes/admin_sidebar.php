<aside class="admin-sidebar" id="adminSidebar">
    <nav class="admin-nav">
        <ul>
            <li>
                <a href="dashboard.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'dashboard.php') ? 'active' : ''; ?>">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
            </li>
            <li>
                <a href="manage_products.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'manage_products.php') ? 'active' : ''; ?>">
                    <i class="fas fa-boxes"></i> Kelola Produk
                </a>
            </li>
            <li>
                <a href="manage_orders.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'manage_orders.php') ? 'active' : ''; ?>">
                    <i class="fas fa-shopping-cart"></i> Kelola Pesanan
                </a>
            </li>
            <li>
                <a href="cashier.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'cashier.php') ? 'active' : ''; ?>">
                    <i class="fas fa-cash-register"></i> Kasir (POS)
                </a>
            </li>
            <li>
                <a href="reports.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'reports.php') ? 'active' : ''; ?>">
                    <i class="fas fa-chart-line"></i> Laporan
                </a>
            </li>
            <li>
                <a href="manage_users.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'manage_users.php') ? 'active' : ''; ?>">
                    <i class="fas fa-user-check"></i> Kelola Akun
                </a>
            </li>
            <li>
                <a href="settings.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'settings.php') ? 'active' : ''; ?>">
                    <i class="fas fa-cogs"></i> Pengaturan
                </a>
            </li>
        </ul>
    </nav>
</aside>