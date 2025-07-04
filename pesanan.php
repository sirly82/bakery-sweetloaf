<?php
session_start();
require 'db_connect.php'; // Pastikan path ke db_connect.php benar


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// session_start();
// ... sisa kode pesanan.php



// Cek apakah user sudah login, jika belum redirect ke login page
if (!isset($_SESSION['id'])) { // Gunakan user_id untuk identifikasi unik
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['id'];
$username = $_SESSION['username'];

// Ambil semua data profil sekaligus
$stmt_user = $conn->prepare("SELECT name, phone, address FROM users WHERE id = ?");
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$result_user = $stmt_user->get_result();

if ($result_user->num_rows > 0) {
    $user_data = $result_user->fetch_assoc();
    $nama_user = $user_data['name'];  // Pastikan kolom di database namanya 'name'
    $telepon_user = $user_data['phone'];
    $alamat_user = $user_data['address'];
    
    // Update session untuk dipakai nanti
    $_SESSION['name'] = $nama_user;
} else {
    // Beri nilai default jika data tidak ditemukan
    $nama_user = '';
    $telepon_user = '';
    $alamat_user = '';
}
$stmt_user->close();

// Ambil dari session jika ada
$nama_user = $_SESSION['name'] ?? '';

// Jika nama kosong, ambil dari database
if (empty($nama_user)) {
    $stmt = $conn->prepare("SELECT name FROM users WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $nama_user = $result->fetch_assoc()['name'];
        $_SESSION['name'] = $nama_user;  // Simpan ke session
    }
    $stmt->close();
}

// Ambil item dari keranjang belanja pengguna
$cart_items = [];
$grand_total = 0;

$sql_cart = "
    SELECT 
        k.produk_id,
        k.jumlah, 
        p.nama AS nama_produk, 
        p.harga AS harga_satuan,
        p.foto AS foto_produk
    FROM keranjang k
    JOIN products p ON k.produk_id = p.id
    WHERE k.user_id = ?
";
$stmt_cart = $conn->prepare($sql_cart);
$stmt_cart->bind_param("i", $user_id);
$stmt_cart->execute();
$result_cart = $stmt_cart->get_result();

if ($result_cart->num_rows > 0) {
    while ($row = $result_cart->fetch_assoc()) {
        $subtotal = $row['jumlah'] * $row['harga_satuan'];
        $grand_total += $subtotal;
        $cart_items[] = [
            'produk_id' => $row['produk_id'],
            'nama_produk' => $row['nama_produk'],
            'foto_produk' => $row['foto_produk'],
            'harga_satuan' => $row['harga_satuan'],
            'jumlah' => $row['jumlah'],
            'subtotal' => $subtotal
        ];
    }
}

// Debug hanya di development environment
if (isset($_GET['debug']) && $_GET['debug'] == 'true') {
    echo '<pre style="display:none;">'; // Sembunyikan secara default
    print_r($cart_items);
    echo '</pre>';
}

$stmt_cart->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesanan Anda - SweetLoaf Bakery</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="top-wave-container">
        <img src="assets/border-atas.png">
    </div>

    <header class="main-header">
        <div class="container">
            <div class="logo">
                <img src="assets/logo-home.png" alt="SweetLoaf Logo">
            </div>
            <nav class="main-nav">
                <ul>
                    <li><a href="home.php" class="nav-link">Beranda</a></li>
                    <li><a href="pesanan.php" class="nav-link active">Pesanan</a></li>
                    <li><a href="#" class="nav-link">Kelola Akun</a></li>
                    <?php if (isset($_SESSION['username'])): // Tampilkan tombol keluar hanya jika sudah login ?>
                        <li>
                            <a href="logout.php" class="btn-logout">
                                <i class="fas fa-sign-out-alt"></i> Keluar
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>

    <main>
        <section class="pesanan-section">
            <div class="container">
                <h2 class="section-title">Detail Pesanan Anda</h2>

                <?php if (empty($cart_items)): ?>
                    <p class="empty-cart-message">Keranjang Anda kosong. Silakan tambahkan produk terlebih dahulu.</p>
                    <div class="center-button">
                        <a href="home.php#products" class="btn-primary">Lihat Produk</a>
                    </div>
                <?php else: ?>
                    <div class="order-summary-box">
                        <div class="order-items-list">
                            <?php foreach ($cart_items as $item): ?>
                                <div class="order-item-card">
                                    <div class="item-image">
                                        <img src="uploads/<?php echo htmlspecialchars($item['foto_produk']); ?>" alt="<?php echo htmlspecialchars($item['nama_produk']); ?>">
                                    </div>
                                    <div class="item-details">
                                        <h3><?php echo htmlspecialchars($item['nama_produk']); ?></h3>
                                        <p>Harga Satuan : Rp<?php echo number_format($item['harga_satuan'], 0, ',', '.'); ?></p>

                                        <!-- Form Tambah/Kurang -->
                                        <form action="update_keranjang.php" method="POST" class="quantity-form" style="display: flex; align-items: center; gap: 5px; margin: 5px 0;">
                                            <input type="hidden" name="produk_id" value="<?php echo $item['produk_id']; ?>">
                                            <button type="submit" name="action" value="kurang">-</button>
                                            <span><?php echo htmlspecialchars($item['jumlah']); ?></span>
                                            <button type="submit" name="action" value="tambah">+</button>
                                        </form>

                                        <!-- Form Hapus -->
                                        <form action="update_keranjang.php" method="POST" style="margin-top: 5px;">
                                            <input type="hidden" name="produk_id" value="<?php echo $item['produk_id']; ?>">
                                            <button type="submit" name="action" value="hapus" style="background-color: #e74c3c; color: white; border: none; padding: 5px 10px; border-radius: 4px;">Hapus</button>
                                        </form>

                                        <p class="item-subtotal">Subtotal : Rp<?php echo number_format($item['subtotal'], 0, ',', '.'); ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <!-- Total dan tombol tambah produk -->
                        <div class="order-summary-container">
                            <div class="order-total-summary">
                                <div class="total-row">
                                    <span>Total Belanja :</span>
                                    <span class="total-amount">Rp<?php echo number_format($grand_total, 0, ',', '.'); ?></span>
                                </div>
                            </div>
                            
                            <div class="form-actions" style="margin-top: 20px;">
                                <a href="home.php#products" class="btn-secondary" style="background-color: #ccc; padding: 10px 20px; text-decoration: none; border-radius: 5px;">+ Tambah Produk</a>
                            </div>
                        </div>
                    </div>

                    <div class="delivery-details-form">
                        <h3 class="form-title">Detail Pengiriman</h3>
                        <form action="pembayaran.php" method="POST" class="shipping-form">
                            <div class="form-group">
                                <label for="nama_penerima" class="form-label">Nama Penerima:</label>
                                <input type="text" id="nama_penerima" name="nama_penerima" 
                                    value="<?php echo htmlspecialchars($nama_user ?? ''); ?>" 
                                    class="form-input"
                                    placeholder="Masukkan nama penerima"
                                    required>
                            </div>

                            <div class="form-group">
                                <label for="nomor_telepon" class="form-label">Nomor Telepon:</label>
                                <input type="tel" id="nomor_telepon" name="nomor_telepon" 
                                    value="<?php echo htmlspecialchars($telepon_user ?? ''); ?>" 
                                    class="form-input"
                                    placeholder="Contoh: 081234567890"
                                    pattern="[0-9]{10,13}"
                                    title="Nomor telepon harus 10-13 digit angka"
                                    required>
                            </div>
                            
                            <div class="form-group">
                                <label for="alamat_pengiriman" class="form-label">Alamat Pengiriman:</label>
                                <textarea id="alamat_pengiriman" name="alamat_pengiriman" rows="3" 
                                        class="form-textarea" required><?php echo htmlspecialchars($alamat_user); ?></textarea>
                                <small class="form-note">* Pastikan alamat sudah benar</small>
                            </div>

                            <input type="hidden" name="total_harga" value="<?php echo $grand_total; ?>">
                            <?php foreach ($cart_items as $index => $item): ?>
                                <input type="hidden" name="items[<?php echo $index; ?>][nama_produk]" value="<?php echo htmlspecialchars($item['nama_produk']); ?>">
                                <input type="hidden" name="items[<?php echo $index; ?>][harga_satuan]" value="<?php echo $item['harga_satuan']; ?>">
                                <input type="hidden" name="items[<?php echo $index; ?>][jumlah]" value="<?php echo $item['jumlah']; ?>">
                                <input type="hidden" name="items[<?php echo $index; ?>][subtotal]" value="<?php echo $item['subtotal']; ?>">
                            <?php endforeach; ?>
                            
                            <div class="form-actions-row">
                                <a href="home.php" class="btn-cancel">Kembali</a>
                                <button type="submit" name="submit_order" class="btn-proceed-payment">Lanjutkan ke Pembayaran</button>
                            </div>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <footer>
        <div class="container">
            <div class="footer-left">
                <div class="logo">
                    <img src="assets/logo.png" alt="SweetLoaf Bakery Logo">
                </div>
                <div class="social-media">
                    <a href="#"><i class="fab fa-facebook-f"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-youtube"></i></a>
                </div>
            </div>
            <div class="footer-right">
                <h4>Lokasi :</h4>
                <div class="map-placeholder">
                    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2592.211231097696!2d117.60250684784057!3d3.303678271356732!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x32138bd956b83ecd%3A0x3868bdace8adf78b!2sSweetLoaf%20Bakery!5e0!3m2!1sid!2sid!4v1751377213866!5m2!1sid!2sid" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                </div>
            </div>
        </div>
        <div class="copyright">
            <p>ESKALA Copyright 2025. All Rights Reserved.</p>
        </div>
    </footer>
    <script src="assets/js/cart.js"></script> </body>
</html>
