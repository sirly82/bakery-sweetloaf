<?php
session_start();

// Cek apakah user dan order ada di session (opsional)
if (!isset($_SESSION['id']) /* || !isset($_SESSION['order_details']) */) {
    header("Location: login.php");
    exit();
}

$_SESSION['checkout_completed'] = true;
header("Location: pesanan.php");

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran Diproses</title>
    <link rel="stylesheet" href="assets/styles/payment_processed.css">
</head>
<body>
    <div class="processed-container">
        <h1 class="processed-title">Pesanan Anda Diterima</h1>
        
        <!-- Jika ingin buat 2 versi, aktifkan bagian bawah sesuai kebutuhan -->
        <p class="processed-message">Pembayaran Anda sedang diproses melalui QRIS. Mohon tunggu konfirmasi lebih lanjut.</p>
        <!-- <p class="processed-message">Pesanan Anda telah diterima. Silakan tunggu konfirmasi dari kami.</p> -->

        <div class="processed-icon">
            <img src="assets/images/order-processing.png" alt="Sedang Diproses">
        </div>

        <div class="button-container">
            <button id="goHomeBtn">Kembali ke Beranda</button>
        </div>
    </div>

    <script src="assets/js/payment_processed.js"></script>
</body>
</html>
