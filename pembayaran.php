<?php
session_start();
require 'db_connect.php';

$DEBUG_MODE = true;

if ($DEBUG_MODE) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
} else {
    ini_set('display_errors', 0);
    error_reporting(0);
}

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['submit_order'])) {
        // Data pengiriman
        $nama_penerima = $_POST['nama_penerima'] ?? '';
        $nomor_telepon = $_POST['nomor_telepon'] ?? '';
        $alamat_pengiriman = $_POST['alamat_pengiriman'] ?? '';
        $total_harga = $_POST['total_harga'] ?? '';
        $items = $_POST['items'] ?? [];

        // Validasi sederhana
        if (empty($nama_penerima) || empty($nomor_telepon) || empty($alamat_pengiriman)) {
            $error_message = "Semua data pengiriman harus diisi.";
        } elseif (empty($items) || !is_array($items)) {
            $error_message = "Data produk tidak valid.";
        } else {
            $_SESSION['order_details'] = [
                'nama_penerima' => $nama_penerima,
                'nomor_telepon' => $nomor_telepon,
                'alamat_pengiriman' => $alamat_pengiriman,
                'total_harga' => $total_harga,
                'items' => $_POST['items']
            ];
            
            header("Location: pembayaran.php");
            exit();
        }
    }
    
    if (isset($_POST['payment_method'])) {
        if (!isset($_SESSION['order_details']) || empty($_SESSION['order_details']['items'])) {
            die("Data order tidak ditemukan. Silakan ulangi proses.");
        }

        $selected_method = $_POST['payment_method'];
        $_SESSION['order_details']['payment_method'] = $selected_method;

        $user_id = $_SESSION['id'];
        $order = $_SESSION['order_details'];
        $order_ref = 'ORD' . time();
        $total = $order['total_harga'];
        $created_at = date('Y-m-d H:i:s');

        try {
            if ($selected_method === 'cash') {
                if (empty($selected_method)) {
                    die("Metode pembayaran tidak boleh kosong.");
                }

                // Simpan ke tabel pesanan
                $order_status = 'on_progress';
                $payment_status = 'unpaid';

                $stmt = $conn->prepare("INSERT INTO pesanan (user_id, order_ref, total, payment_type, payment_status, order_status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("isdssss", $user_id, $order_ref, $total, $selected_method, $payment_status, $order_status, $created_at);
                $stmt->execute();
                $pesanan_id = $stmt->insert_id;
        
                // Simpan item pesanan
                $stmt_item = $conn->prepare("INSERT INTO pesanan_items (pesanan_id, produk_id, jumlah, harga) VALUES (?, ?, ?, ?)");
                foreach ($order['items'] as $item) {
                    $stmt_item->bind_param("iiid", $pesanan_id, $item['produk_id'], $item['jumlah'], $item['harga']);
                    $stmt_item->execute();
                }
        
                // Agar keranjang terhapus
                $_SESSION['last_order_id'] = $pesanan_id;
                $_SESSION['checkout_completed'] = true;

                // Hapus order_details karena sudah diproses
                unset($_SESSION['order_details']);

                header("Location: payment_processed.php");
                exit;
            } elseif ($selected_method === 'qris') {
                header("Location: payment_qris.php");
                exit;
            } else {
                $error_message = "Metode pembayaran tidak dikenali.";
            }
        } catch (Exception $e) {
            if ($DEBUG_MODE) {
                echo "<pre>GAGAL SIMPAN KE DATABASE:\n" . $e->getMessage() . "</pre>";
            } else {
                echo "<p style='color:red;text-align:center;'>Terjadi kesalahan saat menyimpan data. Silakan coba lagi nanti.</p>";
            }
            exit();
        }
    }
} elseif (!isset($_SESSION['order_details'])) {
    header("Location: pesanan.php");
    exit();
}

// Jika user dari payment_qris.php lalu memutuskan GANTI ke cash
if (isset($_GET['ganti_ke']) && $_GET['ganti_ke'] === 'cash') {
    if (!isset($_SESSION['order_details']) || empty($_SESSION['order_details']['items'])) {
        die("Data order tidak ditemukan.");
    }

    $user_id = $_SESSION['id'];
    $order = $_SESSION['order_details'];
    $order_ref = 'ORD' . time();
    $total = $order['total_harga'];
    $payment_type = 'cash';
    $payment_status = 'unpaid';
    $order_status = 'on_progress';
    $created_at = date('Y-m-d H:i:s');

    try {
        $stmt = $conn->prepare("INSERT INTO pesanan (user_id, order_ref, total, payment_type, payment_status, order_status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isdssss", $user_id, $order_ref, $total, $payment_type, $payment_status, $order_status, $created_at);

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

        header("Location: payment_processed.php");
        exit;
    } catch (Exception $e) {
        echo "<pre>Gagal menyimpan order cash dari QRIS: " . $e->getMessage() . "</pre>";
        exit;
    }
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

<?php if (isset($error_message)): ?>
    <div class="error-message">
        <?php echo htmlspecialchars($error_message); ?>
    </div>
<?php endif; ?>

</html>