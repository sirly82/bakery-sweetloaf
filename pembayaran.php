<?php
session_start();
require 'db_connect.php';

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['submit_order'])) {
        // Data pengiriman
        $nama_penerima = $_POST['nama_penerima'];
        $nomor_telepon = $_POST['nomor_telepon'];
        $alamat_pengiriman = $_POST['alamat_pengiriman'];
        $total_harga = $_POST['total_harga'];

        // Validasi sederhana (tambahan)
        if (empty($nama_penerima) || empty($nomor_telepon) || empty($alamat_pengiriman)) {
            $error_message = "Semua data pengiriman harus diisi.";
        } else {
            $_SESSION['order_details'] = [
                'nama_penerima' => $nama_penerima,
                'nomor_telepon' => $nomor_telepon,
                'alamat_pengiriman' => $alamat_pengiriman,
                'total_harga' => $total_harga,
                'items' => $_POST['items']
            ];
        }
    } elseif (isset($_POST['payment_method'])) {
        $selected_method = $_POST['payment_method'];

        $_SESSION['order_details']['payment_method'] = $selected_method;

        switch ($selected_method) {
            case 'qris':
                header("Location: payment_qris.php");
                exit();
            case 'cash':
                header("Location: payment_success.php");
                exit();
            default:
                $error_message = "Metode pembayaran tidak dikenali.";
                break;
        }
    }
} elseif (!isset($_SESSION['order_details'])) {
    // Jika akses langsung tanpa data pesanan, redirect kembali
    header("Location: pesanan.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran - SweetLoaf Bakery</title>
    <link rel="stylesheet" href="assets/styles/pembayaran.css">
</head>
<body>
    <!-- <?php include 'header.php'; ?> -->
    
    <div class="payment-container">
        <h1 class="payment-title">Pilih Metode Pembayaran</h1>

        <?php if (isset($error_message)) : ?>
            <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>
        
        <div class="payment-content">
            <div class="payment-card">
            <h2 class="payment-subtitle">Silakan pilih salah satu metode pembayaran</h2>
                <form id="paymentForm" action="" method="POST">
                    <div class="payment-methods">
                        <!-- Metode QRIS -->
                        <div class="payment-method"
                            onclick="selectPaymentMethod('qris')">
                            <div class="payment-inner">
                                <input type="radio" name="payment_method" value="qris" id="qris-radio" 
                                    class="hidden-radio">
                                <img src="assets/images/qris-code.png" alt="QRIS">
                                <label for="qris-radio">QRIS</label>
                            </div>
                        </div>
                        
                        <!-- Metode CASH -->
                        <div class="payment-method"
                            onclick="selectPaymentMethod('cash')">
                            <div class="payment-inner">
                                <input type="radio" name="payment_method" value="cash" id="cash-radio" 
                                class="hidden-radio">
                                <img src="assets/images/cash-payment.png" alt="CASH">
                                <label for="cash-radio">TUNAI</label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="payment-instruction">
                        <p>Klik salah satu metode pembayaran di atas untuk melanjutkan</p>
                        <div class="total-amount">
                            Total: Rp<?php echo number_format($_SESSION['order_details']['total_harga'], 0, ',', '.'); ?>
                        </div>
                    </div>
                </form>
                <div id="loading" style="display:none; text-align:center; margin-top:20px;">
                    <p>Memproses pembayaran...</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- <?php include 'footer.php'; ?> -->
    
    <script src="assets/js/pembayaran.js"></script>
</body>
</html>