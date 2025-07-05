<?php
session_start();
require_once 'db_connect.php';

// Pastikan user sudah login
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['id'];
$pesanan = [];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tentang Kami</title>
    <link rel="stylesheet" href="assets/styles/about.css">
</head>
<body>
    <header>
        <img src="assets/SWEETLOAF.png" alt="Logo Bakery">
        <h1>SweetLoaf Bakery</h1>
    </header>
    <nav>
        <ul>
            <li><a href="home.php">Beranda</a></li>
            <li><a href="pesanan.php">Keranjang</a></li>
            <li><a href="history.php">Pesanan</a></li>
            <li><a href="about.php">Tentang Kami</a></li>
            <li><a href="logout.php" class="logout">Logout</a></li>
        </ul>
    </nav>
    <main>
        <section class="about">
            <h2>Tentang Cake of The Box</h2>
            <p>
                Cake of The Box adalah toko bakery online yang berdedikasi untuk menghadirkan 
                kue-kue segar dan lezat langsung ke pintu rumah Anda. Kami memulai perjalanan 
                ini dengan semangat untuk menyajikan kebahagiaan melalui setiap gigitan.
            </p>
            <p>
                Dengan menggunakan bahan-bahan terbaik dan resep otentik, kami memastikan bahwa 
                setiap produk yang Anda pesan tidak hanya enak tetapi juga dibuat dengan cinta. 
                Kami berkomitmen untuk memberikan layanan terbaik dan produk berkualitas tinggi 
                kepada pelanggan kami.
            </p>
            <h3>Mengapa Memilih Kami?</h3>
            <ul>
                <li>Kualitas Terbaik: Kami hanya menggunakan bahan berkualitas tinggi.</li>
                <li>Pengiriman Cepat: Pesanan Anda akan dikirimkan tepat waktu.</li>
                <li>Variasi Produk: Beragam pilihan kue untuk segala acara.</li>
            </ul>
            <h3>Hubungi Kami</h3>
            <p>
                Jika Anda memiliki pertanyaan atau ingin memesan khusus, jangan ragu untuk 
                menghubungi kami:
            </p>
            <ul>
                <li>Email: <a href="mailto:info@cakeofthebox.com">info@cakeofthebox.com</a></li>
                <li>Telepon: +62 812 3456 7890</li>
                <li>Alamat: Jl. Lezat Nomor 123, Jakarta</li>
            </ul>
        </section>
    </main>
    <footer>
        <div class="footer-content">
            <p>&copy; 2024 Cake of The Box. All Rights Reserved.</p>
            <ul>
                <li><a href="privacy.php">Kebijakan Privasi</a></li>
            </ul>
        </div>
    </footer>
</body>
</html>