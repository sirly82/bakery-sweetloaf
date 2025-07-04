<?php
// admin/dashboard.php

// Pastikan sesi dimulai di awal setiap file yang akan menggunakan $_SESSION
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// 1. Include file otentikasi/otorisasi admin
// File ini akan memeriksa apakah admin sudah login.
// Jika belum, akan redirect ke login_admin.php
require_once 'includes/admin_auth.php';

// 2. Include file koneksi database
// db_connect.php berada di satu level di atas folder admin/
require_once '../db_connect.php';

// Ambil data admin dari sesi
$admin_username = $_SESSION['admin_username'] ?? 'Admin'; // Menggunakan null coalescing untuk keamanan
$admin_id = $_SESSION['admin_id'] ?? null;

// --- Logika untuk Dashboard (Contoh) ---
// Anda bisa menambahkan query database di sini untuk mengambil data yang relevan untuk dashboard,
// seperti jumlah produk, pesanan baru, total pendapatan, dll.

$total_products = 0;
$total_orders = 0;
$total_customers = 0;
$revenue_today = 0;
$total_unpaid_orders = 0;

// Contoh: Ambil jumlah total produk
$sql_products = "SELECT COUNT(*) AS total FROM products";
$result_products = $conn->query($sql_products);
if ($result_products && $result_products->num_rows > 0) {
    $row = $result_products->fetch_assoc();
    $total_products = $row['total'];
}

// Contoh: Ambil jumlah total pesanan
$sql_orders = "SELECT COUNT(*) AS total FROM pesanan";
$result_orders = $conn->query($sql_orders);
if ($result_orders && $result_orders->num_rows > 0) {
    $row = $result_orders->fetch_assoc();
    $total_orders = $row['total'];
}

// Contoh: Ambil jumlah total user (customer)
$sql_users = "SELECT COUNT(*) AS total FROM users WHERE role = 'user'";
$result_users = $conn->query($sql_users);
if ($result_users && $result_users->num_rows > 0) {
    $row = $result_users->fetch_assoc();
    $total_customers = $row['total'];
}

$sql_unpaid = "SELECT COUNT(*) AS total FROM pesanan WHERE payment_status = 'unpaid'";
$result_unpaid = $conn->query($sql_unpaid);
if ($result_unpaid && $result_unpaid->num_rows > 0) {
    $row_unpaid = $result_unpaid->fetch_assoc();
    $total_unpaid_orders = $row_unpaid['total'];
}

// Contoh: Total pendapatan hari ini (asumsi ada kolom 'tanggal_pesanan' dan 'total_harga' di tabel 'pesanan')
// Ini adalah contoh sederhana, mungkin perlu penyesuaian tergantung struktur tabel pesanan Anda.
$today = date('Y-m-d'); // Ini akan menghasilkan tanggal hari ini dalam format YYYY-MM-DD (contoh: '2025-07-04')

// Query untuk mengambil total pendapatan hari ini dari kolom 'created_at'
$sql_revenue_today = "SELECT SUM(total) AS total_revenue FROM pesanan WHERE DATE(created_at) = ?";
$stmt_revenue = $conn->prepare($sql_revenue_today);
$stmt_revenue->bind_param("s", $today); // "s" karena $today adalah string
$stmt_revenue->execute();
$result_revenue = $stmt_revenue->get_result();

$revenue_today = 0; // Inisialisasi default

if ($result_revenue && $result_revenue->num_rows > 0) {
    $row_revenue = $result_revenue->fetch_assoc();
    $revenue_today = $row_revenue['total_revenue'] ?? 0; // Gunakan null coalescing untuk memastikan nilai jika SUM menghasilkan NULL (tidak ada pesanan)
}
$stmt_revenue->close(); // Tutup statement

// Tutup koneksi database setelah selesai mengambil data
$conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - SweetLoaf Bakery</title>
    <link rel="stylesheet" href="admin_style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="wrapper">
        <?php
        // Include header dan sidebar admin
        require_once 'includes/admin_header.php';
        require_once 'includes/admin_sidebar.php';
        ?>

        <main class="admin-main-content">
            <div class="container">
                <h1>Selamat Datang, Admin <?php echo htmlspecialchars($admin_username); ?>!</h1>
                <p>Ringkasan Aktivitas Toko Roti Anda.</p>

                <div class="dashboard-cards">
                    <div class="card">
                        <h3>Total Produk</h3>
                        <p class="card-value"><?php echo $total_products; ?></p>
                        <a href="manage_products.php" class="card-link">Lihat Produk <i class="fas fa-arrow-right"></i></a>
                    </div>
                    <div class="card">
                        <h3>Pesanan Baru</h3>
                        <p class="card-value"><?php echo $total_orders; ?></p>
                        <a href="manage_orders.php" class="card-link">Kelola Pesanan <i class="fas fa-arrow-right"></i></a>
                    </div>
                    <div class="card">
                        <h3>Total Pelanggan</h3>
                        <p class="card-value"><?php echo $total_customers; ?></p>
                        <a href="#" class="card-link">Lihat Pelanggan <i class="fas fa-arrow-right"></i></a>
                    </div>
                    <div class="card">
                        <h3>Pendapatan Hari Ini</h3>
                        <p class="card-value">Rp <?php echo number_format($revenue_today, 0, ',', '.'); ?></p>
                        <a href="reports.php" class="card-link">Lihat Laporan <i class="fas fa-arrow-right"></i></a>
                    </div>
                    <div class="card">
                        <h3>Pesanan Belum Dibayar</h3>
                        <p class="card-value"><?php echo $total_unpaid_orders; ?></p>
                        <a href="manage_orders.php?filter=unpaid" class="card-link">Lihat Detail <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>

                </div>
        </main>

        <?php require_once 'includes/admin_footer.php'; ?>
    </div>
</body>
</html>