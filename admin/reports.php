<?php
// admin/reports.php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once 'includes/admin_auth.php';
require_once '../db_connect.php';

$current_admin_username = $_SESSION['admin_username'] ?? 'Admin';

$total_revenue = 0;
$revenue_today = 0;
$revenue_this_month = 0;
$revenue_this_year = 0;
$total_orders = 0;
$recent_orders = [];

// Produk Terlaris (Top Selling Products)
$top_products = [];

$sql_top_products = "
    SELECT pr.nama, SUM(pi.jumlah) AS total_terjual
    FROM pesanan_items pi
    JOIN products pr ON pi.produk_id = pr.id
    JOIN pesanan p ON pi.pesanan_id = p.order_id
    WHERE p.order_status = 'completed'
    GROUP BY pi.produk_id
    ORDER BY total_terjual DESC
    LIMIT 5
";
$result_top_products = $conn->query($sql_top_products);
if ($result_top_products && $result_top_products->num_rows > 0) {
    while ($row = $result_top_products->fetch_assoc()) {
        $top_products[] = $row;
    }
}

// Total Pendapatan Keseluruhan
$sql_total_revenue = "SELECT SUM(total) AS total FROM pesanan WHERE order_status = 'completed'";
$result_total_revenue = $conn->query($sql_total_revenue);
if ($result_total_revenue && $result_total_revenue->num_rows > 0) {
    $row = $result_total_revenue->fetch_assoc();
    $total_revenue = $row['total'] ?? 0;
}

// Pendapatan Hari Ini
$today = date('Y-m-d');
$sql_revenue_today = "SELECT SUM(total) AS total FROM pesanan WHERE DATE(created_at) = ? AND order_status = 'completed'";
$stmt_revenue_today = $conn->prepare($sql_revenue_today);
$stmt_revenue_today->bind_param("s", $today);
$stmt_revenue_today->execute();
$result_revenue_today = $stmt_revenue_today->get_result();
if ($result_revenue_today && $result_revenue_today->num_rows > 0) {
    $row = $result_revenue_today->fetch_assoc();
    $revenue_today = $row['total'] ?? 0;
}
$stmt_revenue_today->close();

// Pendapatan Bulan Ini
$this_month_start = date('Y-m-01');
$this_month_end = date('Y-m-t');
$sql_revenue_this_month = "SELECT SUM(total) AS total FROM pesanan WHERE created_at BETWEEN ? AND ? AND order_status = 'completed'";
$stmt_revenue_this_month = $conn->prepare($sql_revenue_this_month);
$stmt_revenue_this_month->bind_param("ss", $this_month_start, $this_month_end);
$stmt_revenue_this_month->execute();
$result_revenue_this_month = $stmt_revenue_this_month->get_result();
if ($result_revenue_this_month && $result_revenue_this_month->num_rows > 0) {
    $row = $result_revenue_this_month->fetch_assoc();
    $revenue_this_month = $row['total'] ?? 0;
}
$stmt_revenue_this_month->close();

// Pendapatan Tahun Ini
$this_year_start = date('Y-01-01');
$this_year_end = date('Y-12-31');
$sql_revenue_this_year = "SELECT SUM(total) AS total FROM pesanan WHERE created_at BETWEEN ? AND ? AND order_status = 'completed'";
$stmt_revenue_this_year = $conn->prepare($sql_revenue_this_year);
$stmt_revenue_this_year->bind_param("ss", $this_year_start, $this_year_end);
$stmt_revenue_this_year->execute();
$result_revenue_this_year = $stmt_revenue_this_year->get_result();
if ($result_revenue_this_year && $result_revenue_this_year->num_rows > 0) {
    $row = $result_revenue_this_year->fetch_assoc();
    $revenue_this_year = $row['total'] ?? 0;
}
$stmt_revenue_this_year->close();

// Total Pesanan Selesai
$sql_total_orders = "SELECT COUNT(*) AS total FROM pesanan WHERE order_status = 'completed'";
$result_total_orders = $conn->query($sql_total_orders);
if ($result_total_orders && $result_total_orders->num_rows > 0) {
    $row = $result_total_orders->fetch_assoc();
    $total_orders = $row['total'] ?? 0;
}

// 10 Pesanan Terakhir
$sql_recent_orders = "
    SELECT p.order_id, u.name AS nama_pengguna, p.total AS total_harga, p.created_at
    FROM pesanan p
    JOIN users u ON p.user_id = u.id
    WHERE p.order_status = 'completed'
    ORDER BY p.created_at DESC
    LIMIT 10
";
$result_recent_orders = $conn->query($sql_recent_orders);
if ($result_recent_orders && $result_recent_orders->num_rows > 0) {
    while ($row = $result_recent_orders->fetch_assoc()) {
        $recent_orders[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan - SweetLoaf Bakery Admin</title>
    <link rel="stylesheet" href="assets/styles/admin_style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="wrapper">
        <?php require_once 'includes/admin_header.php'; ?>
        <?php require_once 'includes/admin_sidebar.php'; ?>

        <main class="admin-main-content">
            <section class="reports-section">
                <h2><i class="fas fa-chart-line"></i> Laporan Penjualan</h2>

                <div class="report-summary-cards">
                    <div class="card">
                        <h3>Total Pendapatan</h3>
                        <p class="card-value large-value">Rp <?php echo number_format($total_revenue, 0, ',', '.'); ?></p>
                    </div>
                    <div class="card">
                        <h3>Pendapatan Hari Ini</h3>
                        <p class="card-value">Rp <?php echo number_format($revenue_today, 0, ',', '.'); ?></p>
                    </div>
                    <div class="card">
                        <h3>Pendapatan Bulan Ini</h3>
                        <p class="card-value">Rp <?php echo number_format($revenue_this_month, 0, ',', '.'); ?></p>
                    </div>
                    <div class="card">
                        <h3>Pendapatan Tahun Ini</h3>
                        <p class="card-value">Rp <?php echo number_format($revenue_this_year, 0, ',', '.'); ?></p>
                    </div>
                    <div class="card">
                        <h3>Total Pesanan Selesai</h3>
                        <p class="card-value"><?php echo number_format($total_orders, 0, ',', '.'); ?></p>
                    </div>
                </div>

                <div class="report-details-section">
                    <div class="detail-card">
                        <h3>Produk Terlaris</h3>
                        <?php if (!empty($top_products)): ?>
                            <table>
                                <thead>
                                    <tr>
                                        <th>Produk</th>
                                        <th>Jumlah Terjual</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($top_products as $product): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($product['nama']); ?></td>
                                            <td><?php echo htmlspecialchars($product['total_terjual']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <p class="no-data-message">Belum ada data penjualan produk.</p>
                        <?php endif; ?>
                    </div>

                    <div class="detail-card">
                        <h3>10 Pesanan Terakhir</h3>
                        <?php if (!empty($recent_orders)): ?>
                            <table>
                                <thead>
                                    <tr>
                                        <th>ID Pesanan</th>
                                        <th>Pelanggan</th>
                                        <th>Total</th>
                                        <th>Tanggal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_orders as $order): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($order['order_id']); ?></td>
                                            <td><?php echo htmlspecialchars($order['nama_pengguna']); ?></td>
                                            <td>Rp <?php echo number_format($order['total_harga'], 0, ',', '.'); ?></td>
                                            <td><?php echo date('d M Y H:i', strtotime($order['created_at'])); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <p class="no-data-message">Belum ada pesanan selesai yang tercatat.</p>
                        <?php endif; ?>
                    </div>
                </div>

            </section>
        </main>

        <?php require_once 'includes/admin_footer.php'; ?>
    </div>
    <script src="assets/js/admin.js"></script>
</body>
</html>
