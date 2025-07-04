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

        // Validasi sederhana
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

            echo "<pre>POST items:\n";
            print_r($_POST['items']);
            echo "</pre>";
            
            header("Location: pembayaran.php");
            exit();
        }
    } elseif (isset($_POST['payment_method'])) {
        $selected_method = $_POST['payment_method'];
        $_SESSION['order_details']['payment_method'] = $selected_method;

        $user_id = $_SESSION['id'];
        $order = $_SESSION['order_details'];
        $order_ref = 'ORD' . time();
        $total = $order['total_harga'];
        $created_at = date('Y-m-d H:i:s');

        // Simpan ke tabel pesanan
        $stmt = $conn->prepare("INSERT INTO pesanan (user_id, order_ref, total, payment_type, payment_status, created_at) VALUES (?, ?, ?, ?, ?, ?)");
        $payment_status = ($selected_method == 'cash') ? 'on_progress' : 'unpaid';
        $stmt->bind_param("isdsss", $user_id, $order_ref, $total, $selected_method, $payment_status, $created_at);
        $stmt->execute();
        $pesanan_id = $stmt->insert_id;

        // Simpan item pesanan
        $stmt_item = $conn->prepare("INSERT INTO pesanan_items (pesanan_id, produk_id, jumlah, harga) VALUES (?, ?, ?, ?)");
        foreach ($order['items'] as $item) {
            $stmt_item->bind_param("iiid", $pesanan_id, $item['produk_id'], $item['jumlah'], $item['harga']);
            $stmt_item->execute();
        }

        $_SESSION['last_order_id'] = $pesanan_id;

        // Agar keranjang terhapus
        $_SESSION['checkout_completed'] = true;

        // Redirect sesuai metode pembayaran
        if ($selected_method === 'qris') {
            header("Location: payment_qris.php");
        } elseif ($selected_method === 'cash') {
            header("Location: payment_processed.php");
        } else {
            $error_message = "Metode pembayaran tidak dikenali.";
        }

        exit();
    }
} elseif (!isset($_SESSION['order_details'])) {
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
                        <button type="submit" name="payment_method" value="qris" class="payment-method">
                            <div class="payment-inner">
                                <img src="assets/images/qris-code.png" alt="QRIS">
                                <label>QRIS</label>
                            </div>
                        </button>

                        <!-- Metode CASH -->
                        <button type="submit" name="payment_method" value="cash" class="payment-method">
                            <div class="payment-inner">
                                <img src="assets/images/cash-payment.png" alt="CASH">
                                <label>TUNAI</label>
                            </div>
                        </button>
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

    <script src="assets/js/pembayaran.js"></script>
</body>
</html>