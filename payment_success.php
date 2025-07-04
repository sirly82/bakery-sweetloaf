<?php
session_start();
unset($_SESSION['order_submitted']); // Reset agar bisa order lagi
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Pembayaran Berhasil</title>
  <link rel="stylesheet" href="assets/styles/payment_success.css">
</head>
<body>
  <div class="success-container">
    <h1>Pembayaran Berhasil!</h1>
    <div class="success-icon">
      <img src="assets/images/checkmark.png" alt="Pembayaran Berhasil" />
    </div>
    <p class="success-message">Terima kasih! Pembayaran Anda telah diproses dengan sukses.</p>
    <div class="button-container ">
      <button id="goCetakBtn">Cetak Struk</button>
      <button id="goHomeBtn">Kembali ke Beranda</button>
    </div>
  </div>

  <script src="assets/js/payment_success.js"></script>
</body>
</html>
