<?php
session_start();
require 'db_connect.php';

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['id'];
$produk_id = $_POST['produk_id'];
$action = $_POST['action'];

if ($action === 'tambah') {
    $stmt = $conn->prepare("UPDATE keranjang SET jumlah = jumlah + 1 WHERE user_id = ? AND produk_id = ?");
    $stmt->bind_param("ii", $user_id, $produk_id);
    $stmt->execute();
} elseif ($action === 'kurang') {
    $stmt = $conn->prepare("UPDATE keranjang SET jumlah = GREATEST(jumlah - 1, 1) WHERE user_id = ? AND produk_id = ?");
    $stmt->bind_param("ii", $user_id, $produk_id);
    $stmt->execute();
} elseif ($action === 'hapus') {
    $stmt = $conn->prepare("DELETE FROM keranjang WHERE user_id = ? AND produk_id = ?");
    $stmt->bind_param("ii", $user_id, $produk_id);
    $stmt->execute();
}

header("Location: pesanan.php");
exit();
?>
