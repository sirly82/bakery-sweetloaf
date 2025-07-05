<?php
session_start();
require_once 'db_connect.php';

// Pastikan user sudah login
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['id'];
$pesanan = [];

try {
    $sql = "SELECT order_id, order_ref, total, payment_type, paid_at, payment_status, order_status, created_at, updated_at 
            FROM pesanan 
            WHERE user_id = ? 
            ORDER BY created_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $pesanan[] = $row;
    }

    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    die("Gagal mengambil data: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Riwayat Pesanan - SweetLoaf Bakery</title>
    <link rel="stylesheet" href="assets/styles/history.css">
</head>
<body>
    <div class="top-wave-container">
        <img src="assets/images/border-atas.png">
    </div>

    <nav>
        <ul>
            <li><a href="home.php">Beranda</a></li>
            <li><a href="pesanan.php">Keranjang</a></li>
            <li><a href="history.php">Pesanan</a></li>
            <li><a href="about.php">Tentang Kami</a></li>
            <li><a href="kelola_akun.php">Kelola Akun</a></li>
            <li><a href="logout.php" class="logout">Logout</a></li>
        </ul>
    </nav>

    <div class="container">
        <h1>Riwayat Pesanan Anda</h1>

        <?php if (empty($pesanan)): ?>
            <p class="kosong">Belum ada pesanan.</p>
        <?php else: ?>
            <table class="tabel-pesanan">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Order Ref</th>
                        <th>Total</th>
                        <th>Pembayaran</th>
                        <th>Status Pembayaran</th>
                        <th>Status Pesanan</th>
                        <th>Tanggal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pesanan as $index => $p): ?>
                        <tr>
                            <td><?= $index + 1 ?></td>
                            <td>#<?= htmlspecialchars($p['order_ref']) ?></td>
                            <td>Rp <?= number_format($p['total'], 0, ',', '.') ?></td>
                            <td><?= htmlspecialchars($p['payment_type'] ?? '-') ?></td>
                            <td class="status <?= $p['payment_status'] ?>">
                                <?= ucfirst($p['payment_status']) ?>
                            </td>
                            <td class="status <?= $p['order_status'] ?>">
                                <?= ucfirst(str_replace('_', ' ', $p['order_status'])) ?>
                            </td>
                            <td><?= date('d M Y H:i', strtotime($p['created_at'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <script src="assets/js/history.js"></script>
</body>
</html>
