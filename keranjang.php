<?php
session_start();
require 'db_connect.php';

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    die("Anda belum login.");
}

// Ambil data dari form POST
$user_id = $_SESSION['user_id'];
$produk_id = $_POST['produk_id'] ?? null;
$jumlah = $_POST['jumlah'] ?? 1;

// Validasi data input
if (!$produk_id || $jumlah <= 0) {
    die("Data produk atau jumlah tidak valid.");
}

// Cek apakah produk benar-benar ada
$cek_produk = $conn->prepare("SELECT stok FROM products WHERE id = ?");
$cek_produk->bind_param("i", $produk_id);
$cek_produk->execute();
$hasil_produk = $cek_produk->get_result();

if ($hasil_produk->num_rows === 0) {
    die("Produk tidak ditemukan.");
}

$row_produk = $hasil_produk->fetch_assoc();
$stok = $row_produk['stok'];

if ($stok < $jumlah) {
    die("Stok tidak mencukupi.");
}

// Cek apakah produk sudah ada di keranjang user → update jumlah
$cek_keranjang = $conn->prepare("SELECT id, jumlah FROM keranjang WHERE user_id = ? AND produk_id = ?");
$cek_keranjang->bind_param("ii", $user_id, $produk_id);
$cek_keranjang->execute();
$hasil_keranjang = $cek_keranjang->get_result();

if ($hasil_keranjang->num_rows > 0) {
    // Produk sudah ada → update jumlah
    $row = $hasil_keranjang->fetch_assoc();
    $id_keranjang = $row['id'];
    $jumlah_baru = $row['jumlah'] + $jumlah;

    $update = $conn->prepare("UPDATE keranjang SET jumlah = ? WHERE id = ?");
    $update->bind_param("ii", $jumlah_baru, $id_keranjang);
    if ($update->execute()) {
        header("Location: home.php?status=updated");
        exit();
    } else {
        die("Gagal memperbarui keranjang: " . $conn->error);
    }
} else {
    // Produk belum ada → insert baru
    $stmt = $conn->prepare("INSERT INTO keranjang (user_id, produk_id, jumlah) VALUES (?, ?, ?)");
    $stmt->bind_param("iii", $user_id, $produk_id, $jumlah);

    if ($stmt->execute()) {
        header("Location: home.php?status=added");
        exit();
    } else {
        die("Gagal menambahkan ke keranjang: " . $conn->error);
    }
}
?>