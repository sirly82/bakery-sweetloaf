<?php
date_default_timezone_set('Asia/Jakarta');
session_start();

if (!isset($_SESSION['id']) || !isset($_SESSION['order_details'])) {
    header("Location: login.php");
    exit();
}

$order_details = $_SESSION['order_details'];

// Saat user klik "Tutup Halaman" (artinya dianggap sudah bayar QRIS)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_order'])) {
    require 'db_connect.php';

    $user_id = $_SESSION['id'];
    $order = $_SESSION['order_details'];
    $order_ref = 'ORD' . time();
    $total = $order['total_harga'];
    $created_at = date('Y-m-d H:i:s');
    $payment_status = 'unpaid';
    $order_status = 'on_progress';
    $payment_type = 'qris';
    $paid_at = NULL;

    try {
        $stmt = $conn->prepare("INSERT INTO pesanan (user_id, order_ref, total, payment_type, payment_status, order_status, created_at, paid_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isdsssss", $user_id, $order_ref, $total, $payment_type, $payment_status, $order_status, $created_at, $paid_at);
        $stmt->execute();
        $pesanan_id = $stmt->insert_id;

        $stmt_item = $conn->prepare("INSERT INTO pesanan_items (pesanan_id, produk_id, jumlah, harga) VALUES (?, ?, ?, ?)");
        foreach ($order['items'] as $item) {
            $stmt_item->bind_param("iiid", $pesanan_id, $item['produk_id'], $item['jumlah'], $item['harga']);
            $stmt_item->execute();
        }

        $_SESSION['last_order_id'] = $pesanan_id;
        $_SESSION['checkout_completed'] = true;
        unset($_SESSION['order_details']);
        $_SESSION['order_submitted'] = true;

        header("Location: payment_success.php");
        exit();
    } catch (Exception $e) {
        echo "<pre>Gagal menyimpan pesanan QRIS: " . $e->getMessage() . "</pre>";
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran QRIS</title>
    <link rel="stylesheet" href="assets/styles/payment_qris.css">
</head>
<body>
    <div class="payment-container">
        <h2>Pembayaran dengan QRIS</h2>
        <p>Silahkan scan QR code berikut untuk melakukan pembayaran</p>
        
        <div class="qris-code">
            <img src="assets/images/qris_pay.png" alt="QRIS Payment">
        </div>
        
        <div class="payment-info">
            <p><strong>Total Pembayaran:</strong> Rp <?php echo number_format($order_details['total_harga'], 0, ',', '.'); ?></p>
            <p><strong>Nama Penerima:</strong> <?php echo htmlspecialchars($order_details['nama_penerima']); ?></p>
        </div>
        
        <p>Setelah pembayaran berhasil, Anda akan menerima notifikasi via email/WhatsApp.</p>
        <div class="payment-buttons">
            <button id="changeMethodBtn">Bayar Cash</button>
            <button type="button" id="finishPaymentBtn">Tutup Halaman</button>
        </div>
    </div>
</body>
<script src="assets/js/payment_qris.js"></script>
</html>