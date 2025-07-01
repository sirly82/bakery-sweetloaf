<?php
session_start();
// Include the database connection file
require 'db_connect.php'; // PASTIKAN NAMA FILE KONEKSI SUDAH BENAR 'db_connect.php'

// Cek apakah user sudah login, jika belum redirect ke login page
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Inisialisasi array untuk menyimpan produk
$products = [];
$best_sellers = []; // Tambahkan ini untuk menyimpan produk best seller
$testimonials = [];

// --- Ambil Produk Best Seller (Contoh: Ambil 4 produk terbaru) ---
$sql_best_sellers = "SELECT id, foto, nama, deskripsi, harga FROM products ORDER BY id DESC LIMIT 4";
$result_best_sellers = $conn->query($sql_best_sellers);

if ($result_best_sellers && $result_best_sellers->num_rows > 0) {
    while ($row = $result_best_sellers->fetch_assoc()) {
        $best_sellers[] = $row;
    }
}


// --- Ambil Semua Produk SweetLoaf ---
$sql_all_products = "SELECT id, foto, nama, deskripsi, harga FROM products";
$result_all_products = $conn->query($sql_all_products);

if ($result_all_products && $result_all_products->num_rows > 0) {
    while ($row = $result_all_products->fetch_assoc()) {
        $products[] = $row;
    }
}

// --- Ambil Testimoni dari Database ---
$sql_testimonials = "SELECT customer_name, comment, rating FROM testimonials ORDER BY created_at DESC LIMIT 6"; // Ambil 6 testimoni terbaru
$result_testimonials = $conn->query($sql_testimonials);

if ($result_testimonials && $result_testimonials->num_rows > 0) {
    while ($row = $result_testimonials->fetch_assoc()) {
        $testimonials[] = $row;
    }
}

// Tutup koneksi database
$conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SweetLoaf Bakery - Beranda</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="top-wave-container">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="#FC8A06" fill-opacity="1" d="M0,320L1440,320L1440,0L0,0Z"></path></svg>
    </div>

    <header class="main-header">
    <div class="container">
        <div class="logo">
            <img src="assets/logo-home.png" alt="SweetLoaf Bakery Logo">
            </div>
        <nav class="main-nav">
            <ul>
                <li><a href="home.php" class="nav-link active">Beranda</a></li>
                <li><a href="pesanan.php" class="nav-link">Keranjang</a></li>
                <li><a href="#" class="nav-link">Pesanan</a></li>
                <li><a href="#" class="nav-link">Tentang Kami</a></li>
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
        <section class="hero-section">
            <div class="container">
                <div class="hero-content">
                    <p>Lagi ngidam yang manis-manis? Kue kami siap mengobati!</p>
                    <h1>Cinta Pada Gigitan Pertama,</h1>
                    <h2 class="heading-pesan-sekarang">Pesan Sekarang!</h2>
                    <a href="pesanan.php" class="btn-primary">Pesan Sekarang</a>
                </div>
                <div class="hero-images">
                    
                </div>
            </div>
        </section>

        <section class="product-section best-sellers">
            <div class="container">
                <h2>Best Seller Kami</h2>
                <div class="product-grid">
                    <?php if (!empty($best_sellers)): ?>
                        <?php foreach ($best_sellers as $product): ?>
                            <div class="product-card">
                                <img src="uploads/<?= htmlspecialchars($product['foto']); ?>" alt="<?= htmlspecialchars($product['nama']); ?>">
                                <h3><?= htmlspecialchars($product['nama']); ?></h3>
                                <p class="description"><?= htmlspecialchars($product['deskripsi']); ?></p>
                                <p class="price">Rp <?= number_format($product['harga'], 0, ',', '.'); ?></p>
                                <button class="btn-add-to-cart" data-product-id="<?= $product['id']; ?>">Tambah ke Keranjang</button>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>Tidak ada produk best seller saat ini.</p>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <section class="product-section all-products">
            <div class="container">
                <h2>Semua Produk SweetLoaf</h2>
                <div class="product-grid">
                    <?php if (!empty($products)): ?>
                        <?php foreach ($products as $product): ?>
                            <div class="product-card">
                                <img src="uploads/<?= htmlspecialchars($product['foto']); ?>" alt="<?= htmlspecialchars($product['nama']); ?>">
                                <h3><?= htmlspecialchars($product['nama']); ?></h3>
                                <p class="description"><?= htmlspecialchars($product['deskripsi']); ?></p>
                                <p class="price">Rp <?= number_format($product['harga'], 0, ',', '.'); ?></p>
                                <button class="btn-add-to-cart" data-product-id="<?= $product['id']; ?>">Tambah ke Keranjang</button>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>Tidak ada produk yang tersedia saat ini.</p>
                    <?php endif; ?>
                </div>
                <div class="view-all-button">
                    <a href="#" class="btn-primary">Lihat Semua Produk</a>
                </div>
            </div>
        </section>

        <section class="testimonials-section">
            <div class="container">
                <h2>Apa Kata Pelanggan Kami?</h2>

                <div class="overall-rating-summary">
                    <h3>Peringkat Keseluruhan</h3>
                    <div class="overall-rating">
                        <span>4.8</span>
                        <div class="stars">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star-half-alt"></i>
                        </div>
                    </div>
                </div>

                <div class="carousel-container">
                    <button class="carousel-button prev" aria-label="Previous testimonial">&#10094;</button>
                    <div class="testimonial-wrapper">
                        <?php if (!empty($testimonials)): ?>
                            <?php foreach ($testimonials as $testimonial): ?>
                                <div class="testimonial-card">
                                    <div class="customer-avatar">
                                        <i class="fas fa-user-circle"></i>
                                    </div>
                                    <p class="quote">"<?= htmlspecialchars($testimonial['comment']); ?>"</p>
                                    <div class="rating">
                                        <?php for ($i = 0; $i < 5; $i++): ?>
                                            <?php if ($i < $testimonial['rating']): ?>
                                                <i class="fas fa-star"></i>
                                            <?php else: ?>
                                                <i class="far fa-star"></i>
                                            <?php endif; ?>
                                        <?php endfor; ?>
                                    </div>
                                    <p class="customer-name">- <?= htmlspecialchars($testimonial['customer_name']); ?></p>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="no-testimonials-message">Belum ada testimoni yang tersedia.</p>
                        <?php endif; ?>
                    </div>
                    <button class="carousel-button next" aria-label="Next testimonial">&#10095;</button>
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
    <script src="assets/js/cart.js"></script>
</body>
</html>