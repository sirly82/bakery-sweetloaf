<?php
session_start();
require 'db_connect.php';

if (!isset($_SESSION['id']) || !isset($_SESSION['order_details'])) {
    header("Location: login.php");
    exit();
}

$order_details = $_SESSION['order_details'];
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
        <button id="finishPaymentBtn">Tutup Halaman</button>
    </div>
</body>
<script src="assets/js/payment_qris.js"></script>
</html>