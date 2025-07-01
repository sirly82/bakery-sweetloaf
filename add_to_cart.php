<?php
session_start();
require 'db_connect.php';

if (!isset($_SESSION['id'])) {
    echo json_encode(['success' => false, 'message' => 'Silakan login terlebih dahulu']);
    exit;
}

if (!isset($_POST['product_id'])) {
    echo json_encode(['success' => false, 'message' => 'Produk tidak valid']);
    exit;
}

$user_id = $_SESSION['id'];
$product_id = $_POST['product_id'];

// Cek apakah produk sudah ada di keranjang
$stmt = $conn->prepare("SELECT * FROM keranjang WHERE user_id = ? AND produk_id = ?");
$stmt->bind_param("ii", $user_id, $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Update jumlah jika produk sudah ada
    $stmt = $conn->prepare("UPDATE keranjang SET jumlah = jumlah + 1 WHERE user_id = ? AND produk_id = ?");
} else {
    // Tambahkan baru jika produk belum ada
    $stmt = $conn->prepare("INSERT INTO keranjang (user_id, produk_id, jumlah) VALUES (?, ?, 1)");
}

$stmt->bind_param("ii", $user_id, $product_id);
if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Gagal menyimpan ke database']);
}

$stmt->close();
$conn->close();
?>