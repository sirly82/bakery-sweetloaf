<?php
// admin/cashier.php

// Aktifkan error reporting (khusus untuk debug)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Tulis log jika terjadi error yang tidak ditangkap
set_exception_handler(function ($e) {
    error_log('Uncaught Exception: ' . $e->getMessage());
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Server Error: ' . $e->getMessage()]);
    } else {
        echo "<pre>Server Error: " . $e->getMessage() . "</pre>";
    }
    exit;
});

require_once 'includes/admin_auth.php';
require_once '../db_connect.php';

// Pastikan sesi dimulai untuk menyimpan keranjang
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Inisialisasi keranjang belanja di sesi jika belum ada
if (!isset($_SESSION['cashier_cart'])) {
    $_SESSION['cashier_cart'] = [];
}

$message = '';
$message_type = '';

// --- Handle AJAX Requests ---
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    header('Content-Type: application/json'); // Respon dalam format JSON

    $response = ['success' => false, 'message' => ''];
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'add_to_cart':
            $product_id = filter_var($_POST['product_id'], FILTER_SANITIZE_NUMBER_INT);
            $qty_to_add = filter_var($_POST['qty'], FILTER_SANITIZE_NUMBER_INT);
            $user_id = $_SESSION['admin_id'] ?? 1; // Sesuaikan jika ada session login admin

            if ($product_id && $qty_to_add > 0) {
                try {
                    $stmt = $conn->prepare("SELECT id, nama, harga, stok FROM products WHERE id = ?");
                    $stmt->bind_param("i", $product_id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $product = $result->fetch_assoc();
                    $stmt->close();

                    if ($product) {
                        $current_qty_in_cart = $_SESSION['cashier_cart'][$product_id]['qty'] ?? 0;
                        $new_qty = $current_qty_in_cart + $qty_to_add;

                        if ($new_qty <= $product['stok']) {
                            // Update session
                            $_SESSION['cashier_cart'][$product_id] = [
                                'id' => $product['id'],
                                'nama' => $product['nama'],
                                'harga' => $product['harga'],
                                'qty' => $new_qty,
                                'stok_tersedia' => $product['stok']
                            ];

                            // Cek apakah produk sudah ada di keranjang DB
                            $cek_stmt = $conn->prepare("SELECT id FROM keranjang WHERE user_id = ? AND produk_id = ?");
                            $cek_stmt->bind_param("ii", $user_id, $product_id);
                            $cek_stmt->execute();
                            $cek_result = $cek_stmt->get_result();

                            if ($cek_result->num_rows > 0) {
                                // Update jumlah
                                $update_stmt = $conn->prepare("UPDATE keranjang SET jumlah = jumlah + ? WHERE user_id = ? AND produk_id = ?");
                                $update_stmt->bind_param("iii", $qty_to_add, $user_id, $product_id);
                                $update_stmt->execute();
                                $update_stmt->close();
                            } else {
                                // Insert baru
                                $insert_stmt = $conn->prepare("INSERT INTO keranjang (user_id, produk_id, jumlah) VALUES (?, ?, ?)");
                                $insert_stmt->bind_param("iii", $user_id, $product_id, $qty_to_add);
                                $insert_stmt->execute();
                                $insert_stmt->close();
                            }
                            $cek_stmt->close();

                            $response['success'] = true;
                            $response['message'] = 'Produk berhasil ditambahkan ke keranjang.';
                        } else {
                            $response['message'] = 'Stok tidak cukup. Maksimum tersedia: ' . ($product['stok'] - $current_qty_in_cart);
                        }
                    } else {
                        $response['message'] = 'Produk tidak ditemukan.';
                    }
                } catch (mysqli_sql_exception $e) {
                    $response['message'] = 'Database error: ' . $e->getMessage();
                }
            } else {
                $response['message'] = 'Data produk tidak valid.';
            }
            break;

        case 'update_cart_qty':
            $product_id = filter_var($_POST['product_id'], FILTER_SANITIZE_NUMBER_INT);
            $new_qty = filter_var($_POST['new_qty'], FILTER_SANITIZE_NUMBER_INT);

            if (isset($_SESSION['cashier_cart'][$product_id])) {
                $product_in_cart = $_SESSION['cashier_cart'][$product_id];
                $available_stock = $product_in_cart['stok_tersedia'];

                if ($new_qty > 0 && $new_qty <= $available_stock) {
                    $_SESSION['cashier_cart'][$product_id]['qty'] = $new_qty;

                    // Update juga di database
                    $user_id = $_SESSION['admin_id'] ?? 1;
                    $stmt = $conn->prepare("UPDATE keranjang SET jumlah = ? WHERE user_id = ? AND produk_id = ?");
                    $stmt->bind_param("iii", $new_qty, $user_id, $product_id);
                    $stmt->execute();
                    $stmt->close();

                    $response['success'] = true;
                    $response['message'] = 'Kuantitas berhasil diperbarui.';
                } elseif ($new_qty <= 0) {
                    unset($_SESSION['cashier_cart'][$product_id]);

                    // Hapus dari database
                    $user_id = $_SESSION['admin_id'] ?? 1;
                    $stmt = $conn->prepare("DELETE FROM keranjang WHERE user_id = ? AND produk_id = ?");
                    $stmt->bind_param("ii", $user_id, $product_id);
                    $stmt->execute();
                    $stmt->close();

                    $response['success'] = true;
                    $response['message'] = 'Produk dihapus dari keranjang.';
                } else {
                    $response['message'] = 'Kuantitas melebihi stok yang tersedia (' . $available_stock . ').';
                }
            } else {
                $response['message'] = 'Produk tidak ditemukan di keranjang.';
            }
            break;

        case 'remove_from_cart':
            $product_id = filter_var($_POST['product_id'], FILTER_SANITIZE_NUMBER_INT);
            if (isset($_SESSION['cashier_cart'][$product_id])) {
                unset($_SESSION['cashier_cart'][$product_id]);

                // Hapus dari database
                $user_id = $_SESSION['admin_id'] ?? 1;
                $stmt = $conn->prepare("DELETE FROM keranjang WHERE user_id = ? AND produk_id = ?");
                $stmt->bind_param("ii", $user_id, $product_id);
                $stmt->execute();
                $stmt->close();

                $response['success'] = true;
                $response['message'] = 'Produk berhasil dihapus dari keranjang.';
            } else {
                $response['message'] = 'Produk tidak ditemukan di keranjang.';
            }
            break;

        case 'finalize_order':
            $customer_name = filter_var($_POST['customer_name'], FILTER_SANITIZE_STRING);
            $notes = filter_var($_POST['notes'], FILTER_SANITIZE_STRING);
            $grand_total = filter_var($_POST['grand_total'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
            $user_id = $_SESSION['admin_id'] ?? 1;

            if (empty($_SESSION['cashier_cart'])) {
                $response['message'] = 'Keranjang belanja kosong. Tidak ada pesanan untuk diproses.';
                echo json_encode($response);
                exit();
            }

            $conn->begin_transaction();
            try {
                // Simpan ke tabel pesanan
                $total_item = array_sum(array_column($_SESSION['cashier_cart'], 'qty'));
                $order_ref = 'ORD' . time(); // unik
                $payment_status = 'paid'; // untuk kasir dianggap langsung lunas
                $created_at = date('Y-m-d H:i:s');

                $stmt_pesanan = $conn->prepare("INSERT INTO pesanan (user_id, nama_pengguna, total_item, total_harga, catatan, status, payment_tipe, order_ref, payment_status, created_at) 
                VALUES (?, ?, ?, ?, ?, 'Selesai', 'cash', ?, ?, ?)");
                $stmt_pesanan->bind_param("isidssss", $user_id, $customer_name, $total_item, $grand_total, $notes, $order_ref, $payment_status, $created_at);
                $stmt_pesanan->execute();
                $pesanan_id = $stmt_pesanan->insert_id;
                $stmt_pesanan->close();

                // Simpan detail item pesanan dan kurangi stok
                foreach ($_SESSION['cashier_cart'] as $item) {
                    // Detail pesanan
                    $stmt_detail = $conn->prepare("INSERT INTO pesanan_satuan (pesanan_id, produk_id, jumlah, harga) VALUES (?, ?, ?, ?)");
                    $stmt_detail->bind_param("iiid", $pesanan_id, $item['id'], $item['qty'], $item['harga']);
                    $stmt_detail->execute();
                    $stmt_detail->close();

                    // Update stok
                    $stmt_stok = $conn->prepare("UPDATE products SET stok = stok - ? WHERE id = ? AND stok >= ?");
                    $stmt_stok->bind_param("iii", $item['qty'], $item['id'], $item['qty']);
                    $stmt_stok->execute();
                    if ($stmt_stok->affected_rows === 0) {
                        throw new Exception("Stok tidak cukup untuk produk ID " . $item['id']);
                    }
                    $stmt_stok->close();
                }

                // Bersihkan keranjang user
                $stmt_clear_cart = $conn->prepare("DELETE FROM keranjang WHERE user_id = ?");
                $stmt_clear_cart->bind_param("i", $user_id);
                $stmt_clear_cart->execute();
                $stmt_clear_cart->close();

                $_SESSION['cashier_cart'] = [];

                $conn->commit();
                $response['success'] = true;
                $response['message'] = 'Pesanan berhasil disimpan!';
                $response['redirect'] = 'cashier.php';
            } catch (Exception $e) {
                $conn->rollback();
                $response['message'] = 'Gagal menyimpan pesanan: ' . $e->getMessage();
            }
            break;

        case 'get_cart':
            // Mengirim kembali keranjang saat ini ke client
            $response['success'] = true;
            $response['cart'] = $_SESSION['cashier_cart'];
            // Hitung grand total
            $current_grand_total = 0;
            foreach ($_SESSION['cashier_cart'] as $item) {
                $current_grand_total += $item['harga'] * $item['qty'];
            }
            $response['grand_total'] = $current_grand_total;
            break;

        default:
            $response['message'] = 'Aksi tidak dikenal.';
            break;
    }
    echo json_encode($response);
    exit(); // Hentikan eksekusi PHP setelah mengirim respons JSON
}

// --- Ambil Daftar Produk untuk Tampilan Awal ---
$products = [];
try {
    $sql = "SELECT id, foto, nama, harga, stok FROM products WHERE stok > 0 ORDER BY nama ASC"; // Hanya tampilkan produk dengan stok > 0
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
    }
    $stmt->close();
} catch (mysqli_sql_exception $e) {
    $message = "Error saat mengambil data produk: " . $e->getMessage();
    $message_type = "danger";
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kasir (POS) - SweetLoaf Bakery Admin</title>
    <link rel="stylesheet" href="assets/styles/admin_style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="wrapper">
        <?php include_once 'includes/admin_header.php'; ?>
        <?php include_once 'includes/admin_sidebar.php'; ?>

        <main class="admin-main-content">
            <div class="admin-content-header">
                <h1><i class="fas fa-cash-register"></i> Kasir (Point of Sale)</h1>
            </div>

            <?php if (!empty($message)): ?>
                <div class="alert alert-<?php echo $message_type; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <div class="cashier-container">
                <div class="product-list-section">
                    <h2>Daftar Produk</h2>
                    <div class="search-bar">
                        <input type="text" id="productSearch" placeholder="Cari produk...">
                    </div>
                    <div class="product-grid">
                        <?php if (!empty($products)): ?>
                            <?php foreach ($products as $product): ?>
                                <div class="product-card" data-id="<?php echo htmlspecialchars($product['id']); ?>" data-nama="<?php echo htmlspecialchars($product['nama']); ?>" data-harga="<?php echo htmlspecialchars($product['harga']); ?>" data-stok="<?php echo htmlspecialchars($product['stok']); ?>">
                                    <?php if (!empty($product['foto']) && file_exists('assets/uploads/products/' . $product['foto'])): ?>
                                        <img src="assets/uploads/products/<?php echo htmlspecialchars($product['foto']); ?>" alt="<?php echo htmlspecialchars($product['nama']); ?>">
                                    <?php else: ?>
                                        <img src="../assets/placeholder.png" alt="No Image">
                                    <?php endif; ?>
                                    <div class="product-info">
                                        <h3 class="product-name"><?php echo htmlspecialchars($product['nama']); ?></h3>
                                        <p class="product-price">Rp <?php echo number_format($product['harga'], 0, ',', '.'); ?></p>
                                        <p class="product-stock">Stok: <?php echo htmlspecialchars($product['stok']); ?></p>
                                        <?php if ($product['stok'] > 0): ?>
                                            <button class="btn-add-to-cart" data-id="<?php echo htmlspecialchars($product['id']); ?>">
                                                <i class="fas fa-cart-plus"></i> Tambah
                                            </button>
                                        <?php else: ?>
                                            <button class="btn-add-to-cart disabled" disabled>
                                                Stok Habis
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="no-products-message">Tidak ada produk tersedia.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="order-summary-section">
                    <h2>Keranjang Pesanan</h2>
                    <div class="cart-items" id="cartItems">
                        <p class="empty-cart-message">Keranjang masih kosong.</p>
                    </div>

                    <div class="order-totals">
                        <div class="total-row">
                            <span>Subtotal:</span>
                            <span id="subtotalAmount">Rp 0</span>
                        </div>
                        <div class="total-row grand-total">
                            <span>Total Akhir:</span>
                            <span id="grandTotalAmount">Rp 0</span>
                        </div>
                    </div>

                    <div class="customer-details">
                        <div class="form-group">
                            <label for="customerName">Nama Pelanggan (Opsional):</label>
                            <input type="text" id="customerName" placeholder="Contoh: Pembeli Langsung">
                        </div>
                        <div class="form-group">
                            <label for="orderNotes">Catatan (Opsional):</label>
                            <textarea id="orderNotes" rows="2" placeholder="Contoh: Tanpa gula, Tambah kartu ucapan"></textarea>
                        </div>
                    </div>

                    <button class="btn-primary btn-finalize-order" id="finalizeOrderBtn">
                        <i class="fas fa-check-circle"></i> Selesaikan Pesanan
                    </button>
                    <button class="btn-danger btn-clear-cart" id="clearCartBtn">
                        <i class="fas fa-times-circle"></i> Bersihkan Keranjang
                    </button>
                </div>
            </div>
        </main>

        <?php include_once 'includes/admin_footer.php'; ?>
    </div>
    <script src="assets/js/cashier.js"></script>
</body>
</html>