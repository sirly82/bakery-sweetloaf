<?php
session_start();
require 'db_connect.php';

// Pastikan user sudah login
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

// Ambil data dari form pesanan
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_order'])) {
    // Data pengiriman
    $nama_penerima = $_POST['nama_penerima'];
    $nomor_telepon = $_POST['nomor_telepon'];
    $alamat_pengiriman = $_POST['alamat_pengiriman'];
    $total_harga = $_POST['total_harga'];
    
    // Simpan data pengiriman ke session untuk digunakan nanti
    $_SESSION['order_details'] = [
        'nama_penerima' => $nama_penerima,
        'nomor_telepon' => $nomor_telepon,
        'alamat_pengiriman' => $alamat_pengiriman,
        'total_harga' => $total_harga,
        'items' => $_POST['items'] // Array dari item keranjang
    ];
    
    // Jika ingin menyimpan ke database, bisa dilakukan di sini
    // atau setelah pembayaran selesai
} else {
    // Jika akses langsung tanpa melalui form, redirect kembali
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
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="top-wave-container">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320">
            <path fill="#FC8A06" fill-opacity="1" d="M0,288L80,266.7C160,245,320,203,480,186.7C640,171,800,181,960,165.3C1120,149,1280,107,1360,85.3L1440,64L1440,0L1360,0C1280,0,1120,0,960,0C800,0,640,0,480,0C320,0,160,0,80,0L0,0Z"></path>
        </svg>
    </div>

    <header class="main-header">
        <div class="container">
            <div class="logo">
                <img src="assets/logo-home.png" alt="SweetLoaf Logo">
            </div>
            <nav class="main-nav">
                <ul class="nav-links">
                    <li><a href="home.php">Home</a></li>
                    <li><a href="#">Produk</a></li>
                    <li><a href="#">Tentang Kami</a></li>
                    <li><a href="#">Kontak</a></li>
                </ul>
            </nav>
            <div class="header-actions">
                <a href="#" class="cart-icon"><i class="fas fa-shopping-cart"></i></a>
                <div class="user-info">
                    <span class="username"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                    <a href="logout.php" class="btn-logout">Logout</a>
                </div>
            </div>
            <div class="menu-icon">
                <i class="fas fa-bars"></i>
            </div>
        </div>
    </header>

    <main>
        <section class="payment-section">
            <div class="container">
                <h2 class="section-title">Metode Pembayaran</h2>
                
                <div class="payment-container">
                    <div class="order-summary">
                        <h3>Ringkasan Pesanan</h3>
                        <div class="shipping-details">
                            <p><strong>Nama Penerima:</strong> <?php echo htmlspecialchars($nama_penerima); ?></p>
                            <p><strong>Nomor Telepon:</strong> <?php echo htmlspecialchars($nomor_telepon); ?></p>
                            <p><strong>Alamat Pengiriman:</strong> <?php echo nl2br(htmlspecialchars($alamat_pengiriman)); ?></p>
                        </div>
                        
                        <div class="order-items">
                            <h4>Item Pesanan:</h4>
                            <?php foreach ($_POST['items'] as $item): ?>
                                <div class="order-item">
                                    <p><?php echo htmlspecialchars($item['nama_produk']); ?> 
                                    (<?php echo $item['jumlah']; ?> x Rp <?php echo number_format($item['harga_satuan'], 0, ',', '.'); ?>)</p>
                                    <p class="item-subtotal">Rp <?php echo number_format($item['subtotal'], 0, ',', '.'); ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="order-total">
                            <p><strong>Total Pembayaran:</strong></p>
                            <p class="total-amount">Rp <?php echo number_format($total_harga, 0, ',', '.'); ?></p>
                        </div>
                    </div>
                    
                    <div class="payment-methods">
                        <h3>Pilih Metode Pembayaran</h3>
                        <form action="process_payment.php" method="POST" class="payment-form">
                            <div class="payment-option">
                                <input type="radio" id="bank_transfer" name="payment_method" value="bank_transfer" checked>
                                <label for="bank_transfer">
                                    <i class="fas fa-university"></i> Transfer Bank
                                </label>
                            </div>
                            
                            <div class="payment-option">
                                <input type="radio" id="gopay" name="payment_method" value="gopay">
                                <label for="gopay">
                                    <i class="fab fa-google-pay"></i> GoPay
                                </label>
                            </div>
                            
                            <div class="payment-option">
                                <input type="radio" id="ovo" name="payment_method" value="ovo">
                                <label for="ovo">
                                    <i class="fas fa-mobile-alt"></i> OVO
                                </label>
                            </div>
                            
                            <div class="payment-option">
                                <input type="radio" id="dana" name="payment_method" value="dana">
                                <label for="dana">
                                    <i class="fas fa-wallet"></i> DANA
                                </label>
                            </div>
                            
                            <!-- Sertakan kembali semua data order sebagai hidden fields -->
                            <input type="hidden" name="nama_penerima" value="<?php echo htmlspecialchars($nama_penerima); ?>">
                            <input type="hidden" name="nomor_telepon" value="<?php echo htmlspecialchars($nomor_telepon); ?>">
                            <input type="hidden" name="alamat_pengiriman" value="<?php echo htmlspecialchars($alamat_pengiriman); ?>">
                            <input type="hidden" name="total_harga" value="<?php echo $total_harga; ?>">
                            
                            <div class="form-actions">
                                <button type="button" class="btn-secondary" onclick="window.location.href='pesanan.php'">Kembali</button>
                                <button type="submit" name="process_payment" class="btn-primary">Bayar Sekarang</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer>
        <div class="container">
            <div class="footer-left">
                <div class="logo">
                    <img src="assets/SWEETLOAF.png" alt="SweetLoaf Bakery Logo">
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
                    <img src="assets/map-placeholder.png" alt="Map Location">
                </div>
            </div>
        </div>
        <div class="copyright">
            <p>ESKALA Copyright 2025. All Rights Reserved.</p>
        </div>
    </footer>
</body>
</html>

<!-- <!DOCTYPE html>
<html lang="id">
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran - SweetLoaf Bakery</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="register.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        /* Payment Page Specific Styles */
        .payment-section {
            padding: 40px 0;
            min-height: calc(100vh - 300px);
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .payment-container {
            background-color: #fff;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 600px;
            margin: 20px;
        }

        .payment-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .payment-header h2 {
            color: #333;
            font-size: 28px;
            margin-bottom: 10px;
        }

        .payment-info {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
        }

        .payment-info p {
            margin: 8px 0;
            font-size: 16px;
        }

        .total-amount {
            font-size: 24px;
            font-weight: 700;
            color: #FC8A06;
            margin: 15px 0;
        }

        .payment-method {
            margin: 30px 0;
        }

        .payment-method h3 {
            text-align: center;
            margin-bottom: 20px;
            color: #333;
            font-size: 22px;
        }

        .qris-container {
            text-align: center;
            margin: 25px 0;
        }

        .qris-image {
            width: 220px;
            height: 220px;
            border: 1px solid #ddd;
            border-radius: 10px;
            padding: 15px;
            background: white;
            margin: 0 auto;
        }

        .instructions {
            background: #f5f5f5;
            padding: 20px;
            border-radius: 10px;
            margin-top: 25px;
        }

        .instructions h4 {
            margin-top: 0;
            color: #444;
        }

        .instructions ol {
            padding-left: 20px;
        }

        .instructions li {
            margin-bottom: 8px;
            line-height: 1.5;
        }

        .btn-confirm-payment {
            width: 100%;
            padding: 15px;
            font-size: 18px;
            font-weight: 600;
            margin-top: 20px;
            background-color: #FC8A06;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .btn-confirm-payment:hover {
            background-color: #e07d05;
        }

        @media (max-width: 768px) {
            .payment-container {
                padding: 25px;
                margin: 15px;
            }
            
            .payment-header h2 {
                font-size: 24px;
            }
            
            .qris-image {
                width: 180px;
                height: 180px;
            }
        }
    </style>
</head>
<body>
    <div class="top-wave-container">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320">
            <path fill="#FC8A06" fill-opacity="1" d="M0,288L80,266.7C160,245,320,203,480,186.7C640,171,800,181,960,165.3C1120,149,1280,107,1360,85.3L1440,64L1440,0L1360,0C1280,0,1120,0,960,0C800,0,640,0,480,0C320,0,160,0,80,0L0,0Z"></path>
        </svg>
    </div>

    <header class="main-header">
        <div class="container">
            <div class="logo">
                <img src="assets/logo-home.png" alt="SweetLoaf Logo">
            </div>
            <nav class="main-nav">
                <ul class="nav-links">
                    <li><a href="home.php">Home</a></li>
                    <li><a href="#">Produk</a></li>
                    <li><a href="#">Tentang Kami</a></li>
                    <li><a href="#">Kontak</a></li>
                </ul>
            </nav>
            <div class="header-actions">
                <a href="pesanan.php" class="cart-icon"><i class="fas fa-shopping-cart"></i></a>
                <div class="user-info">
                    <span class="username"><?php echo htmlspecialchars($username); ?></span>
                    <a href="logout.php" class="btn-logout">Logout</a>
                </div>
            </div>
            <div class="menu-icon">
                <i class="fas fa-bars"></i>
            </div>
        </div>
    </header>

    <main>
        <section class="payment-section">
            <div class="payment-container">
                <div class="payment-header">
                    <h2>Pembayaran Pesanan</h2>
                    <p>Silakan selesaikan pembayaran Anda</p>
                </div>

                <div class="payment-info">
                    <p><strong>Nomor Pesanan:</strong> <?php echo htmlspecialchars($order_id); ?></p>
                    <p><strong>Metode Pembayaran:</strong> QRIS</p>
                    <div class="total-amount">Rp <?php echo number_format($grand_total, 0, ',', '.'); ?></div>
                </div>

                <div class="payment-method">
                    <h3>Scan Kode QRIS</h3>
                    <div class="qris-container">
                        <img src="assets/QRIS.jpg" alt="QRIS Payment" class="qris-image">
                        <p>Gunakan aplikasi pembayaran untuk scan QR code</p>
                    </div>

                    <div class="instructions">
                        <h4>Petunjuk Pembayaran:</h4>
                        <ol>
                            <li>Buka aplikasi pembayaran (GoPay, OVO, DANA, atau mobile banking)</li>
                            <li>Pilih menu "Scan QR" atau "Bayar dengan QRIS"</li>
                            <li>Arahkan kamera ke kode QR di atas</li>
                            <li>Pastikan nominal: <strong>Rp <?php echo number_format($grand_total, 0, ',', '.'); ?></strong></li>
                            <li>Konfirmasi pembayaran</li>
                            <li>Tunggu hingga mendapatkan notifikasi pembayaran berhasil</li>
                        </ol>
                    </div>
                </div>

                <form action="process_payment_confirmation.php" method="POST">
                    <input type="hidden" name="order_id" value="<?php echo htmlspecialchars($order_id); ?>">
                    <input type="hidden" name="total_pembayaran" value="<?php echo $grand_total; ?>">
                    <button type="submit" class="btn-confirm-payment">Konfirmasi Pembayaran</button>
                </form>
            </div>
        </section>
    </main>

    <footer>
        <div class="container">
            <div class="footer-left">
                <div class="logo">
                    <img src="assets/SWEETLOAF.png" alt="SweetLoaf Bakery Logo">
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
                    <img src="assets/map-placeholder.png" alt="Map Location">
                </div>
            </div>
        </div>
        <div class="copyright">
            <p>ESKALA Copyright 2025. All Rights Reserved.</p>
        </div>
    </footer>
</body>
</html> -->