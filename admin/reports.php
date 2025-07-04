<?php
// admin/reports.php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once 'includes/admin_auth.php'; // Memastikan hanya admin yang bisa mengakses
require_once '../db_connect.php'; // Koneksi ke database

// Ambil username admin dari sesi
$current_admin_username = $_SESSION['admin_username'] ?? 'Admin';

// --- Logika Pengambilan Data Laporan ---

// Inisialisasi variabel untuk data laporan
$total_revenue = 0;
$revenue_today = 0;
$revenue_this_month = 0;
$revenue_this_year = 0;
$total_orders = 0;
// $total_products_sold = 0; // Tidak bisa dihitung langsung dari skema DB saat ini
// $top_products = [];       // Tidak bisa dihitung langsung dari skema DB saat ini
$recent_orders = [];

// 1. Total Pendapatan Keseluruhan
// Menggunakan tabel 'pesanan' dan kolom 'total_harga' dengan status 'Selesai'
$sql_total_revenue = "SELECT SUM(total_harga) AS total FROM pesanan WHERE status = 'Selesai'";
$result_total_revenue = $conn->query($sql_total_revenue);
if ($result_total_revenue && $result_total_revenue->num_rows > 0) {
    $row = $result_total_revenue->fetch_assoc();
    $total_revenue = $row['total'] ?? 0;
}

// 2. Pendapatan Hari Ini
// Menggunakan tabel 'pesanan' dan kolom 'created_at' (tanggal pesanan dibuat) dengan status 'Selesai'
$today = date('Y-m-d');
$sql_revenue_today = "SELECT SUM(total_harga) AS total FROM pesanan WHERE DATE(created_at) = ? AND status = 'Selesai'";
$stmt_revenue_today = $conn->prepare($sql_revenue_today);
$stmt_revenue_today->bind_param("s", $today);
$stmt_revenue_today->execute();
$result_revenue_today = $stmt_revenue_today->get_result();
if ($result_revenue_today && $result_revenue_today->num_rows > 0) {
    $row = $result_revenue_today->fetch_assoc();
    $revenue_today = $row['total'] ?? 0;
}
$stmt_revenue_today->close();

// 3. Pendapatan Bulan Ini
// Menggunakan tabel 'pesanan' dan kolom 'created_at' dengan status 'Selesai'
$this_month_start = date('Y-m-01');
$this_month_end = date('Y-m-t');
$sql_revenue_this_month = "SELECT SUM(total_harga) AS total FROM pesanan WHERE created_at BETWEEN ? AND ? AND status = 'Selesai'";
$stmt_revenue_this_month = $conn->prepare($sql_revenue_this_month);
$stmt_revenue_this_month->bind_param("ss", $this_month_start, $this_month_end);
$stmt_revenue_this_month->execute();
$result_revenue_this_month = $stmt_revenue_this_month->get_result();
if ($result_revenue_this_month && $result_revenue_this_month->num_rows > 0) {
    $row = $result_revenue_this_month->fetch_assoc();
    $revenue_this_month = $row['total'] ?? 0;
}
$stmt_revenue_this_month->close();

// 4. Pendapatan Tahun Ini
// Menggunakan tabel 'pesanan' dan kolom 'created_at' dengan status 'Selesai'
$this_year_start = date('Y-01-01');
$this_year_end = date('Y-12-31');
$sql_revenue_this_year = "SELECT SUM(total_harga) AS total FROM pesanan WHERE created_at BETWEEN ? AND ? AND status = 'Selesai'";
$stmt_revenue_this_year = $conn->prepare($sql_revenue_this_year);
$stmt_revenue_this_year->bind_param("ss", $this_year_start, $this_year_end);
$stmt_revenue_this_year->execute();
$result_revenue_this_year = $stmt_revenue_this_year->get_result();
if ($result_revenue_this_year && $result_revenue_this_year->num_rows > 0) {
    $row = $result_revenue_this_year->fetch_assoc();
    $revenue_this_year = $row['total'] ?? 0;
}
$stmt_revenue_this_year->close();

// 5. Total Pesanan Selesai
// Menggunakan tabel 'pesanan' dengan status 'Selesai'
$sql_total_orders = "SELECT COUNT(*) AS total FROM pesanan WHERE status = 'Selesai'";
$result_total_orders = $conn->query($sql_total_orders);
if ($result_total_orders && $result_total_orders->num_rows > 0) {
    $row = $result_total_orders->fetch_assoc();
    $total_orders = $row['total'] ?? 0;
}

// 6. Produk Terlaris (Tidak dapat dihitung langsung dengan skema DB saat ini)
// Data produk dan kuantitasnya disimpan sebagai TEXT di kolom 'pesanan' (contoh: 'Strawberry Cheesecake (1), Strawberry Mini Tart (2)')
// Mengurai string ini dengan SQL sangat tidak efisien dan tidak disarankan.
// Untuk laporan produk terlaris yang akurat, database idealnya memiliki tabel 'order_items' terpisah
// yang menghubungkan 'pesanan' dengan 'products' dan 'quantity'.
$top_products = []; // Biarkan kosong atau tambahkan logika parsing PHP jika sangat diperlukan (lebih kompleks)
// Contoh jika ingin parsing di PHP (ini akan membutuhkan lebih banyak resource untuk data besar):
/*
$all_completed_orders_items = [];
$sql_all_completed = "SELECT pesanan FROM pesanan WHERE status = 'Selesai'";
$result_all_completed = $conn->query($sql_all_completed);
if ($result_all_completed && $result_all_completed->num_rows > 0) {
    while ($row = $result_all_completed->fetch_assoc()) {
        // Contoh parsing sederhana, perlu penanganan error lebih baik
        preg_match_all('/(.*?)\s*\((\d+)\)/', $row['pesanan'], $matches, PREG_SET_ORDER);
        foreach ($matches as $match) {
            $product_name = trim($match[1]);
            $quantity = (int)$match[2];
            $all_completed_orders_items[$product_name] = ($all_completed_orders_items[$product_name] ?? 0) + $quantity;
        }
    }
}
arsort($all_completed_orders_items); // Urutkan dari yang terbesar
$top_products = array_slice($all_completed_orders_items, 0, 5, true); // Ambil top 5

// Format ulang untuk tampilan di tabel
$formatted_top_products = [];
foreach ($top_products as $name => $quantity) {
    $formatted_top_products[] = ['nama' => $name, 'total_quantity_sold' => $quantity];
}
$top_products = $formatted_top_products;
*/


// 7. Pesanan Terakhir (Misal 10 Pesanan Terbaru yang Selesai)
// Menggunakan tabel 'pesanan' dan kolom 'id_pesanan', 'nama_pengguna', 'total_harga', 'created_at'
$sql_recent_orders = "
    SELECT id_pesanan, nama_pengguna, total_harga, created_at
    FROM pesanan
    WHERE status = 'Selesai'
    ORDER BY created_at DESC
    LIMIT 10";
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan - SweetLoaf Bakery Admin</title>
    <link rel="stylesheet" href="admin_style.css">
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
                        <h3>Produk Terlaris (Tidak Tersedia)</h3>
                        <p class="no-data-message">
                            Untuk laporan produk terlaris, struktur database saat ini (item pesanan disimpan sebagai teks) membuat pengambilan data ini sangat sulit atau tidak mungkin dilakukan secara efisien dengan query SQL.
                            Disarankan untuk menambahkan tabel terpisah `order_items` untuk menyimpan setiap produk dalam pesanan secara individual.
                        </p>
                        <?php /*
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
                                            <td><?php echo number_format($product['total_quantity_sold'], 0, ',', '.'); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <p class="no-data-message">Belum ada data produk terlaris.</p>
                        <?php endif; ?>
                        */ ?>
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
                                            <td><?php echo htmlspecialchars($order['id_pesanan']); ?></td>
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
    <script src="admin.js"></script>
</body>
</html>