<?php
date_default_timezone_set('Asia/Jakarta');
session_start();

if (!isset($_SESSION['id']) || !isset($_SESSION['order_details'])) {
    header("Location: login.php");
    exit();
}

$order_details = $_SESSION['order_details'];

// Saat tombol "Tutup Halaman" ditekan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_order'])) {
    // Jangan simpan ke database lagi, cukup redirect
    unset($_SESSION['order_details']); // Bersihkan session order agar tidak bisa diulang
    $_SESSION['order_submitted'] = true;
    header("Location: payment_success.php");
    exit();
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
            <form method="POST" style="text-align:center;">
                <input type="hidden" name="save_order" value="1">
                <button type="submit" id="finishPaymentBtn">Tutup Halaman</button>
            </form>
            <!-- <button id="finishPaymentBtn">Tutup Halaman</button> -->
        </div>
    </div>
</body>
<script src="assets/js/payment_qris.js"></script>
</html>