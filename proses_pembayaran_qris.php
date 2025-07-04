<?php
session_start();
require 'db_connect.php';

if (!isset($_SESSION['id']) || !isset($_SESSION['order_details'])) {
    echo json_encode(['success' => false, 'message' => 'Session tidak valid']);
    exit;
}

$user_id = $_SESSION['id'];
$order = $_SESSION['order_details'];

// Generate order_ref unik
$order_ref = 'ORD' . strtoupper(uniqid());
$total = $order['total_harga'];
$payment_type = 'qris';
$status = 'paid';
$payment_status = 'on_progress';
$payment_method = 'manual';
$paid_at = date('Y-m-d H:i:s');

// Simpan ke tabel pesanan
$sql = "INSERT INTO pesanan (user_id, order_ref, total, status, payment_method, payment_type, paid_at, payment_status)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("isssssss", $user_id, $order_ref, $total, $status, $payment_method, $payment_type, $paid_at, $payment_status);

if ($stmt->execute()) {
    $pesanan_id = $stmt->insert_id;

    // Simpan item pesanan (jika ada)
    if (isset($order['items']) && is_array($order['items'])) {
        $stmt_item = $conn->prepare("INSERT INTO pesanan_items (pesanan_id, produk_id, jumlah, harga) VALUES (?, ?, ?, ?)");
        foreach ($order['items'] as $item) {
            $stmt_item->bind_param("iiid", $pesanan_id, $item['produk_id'], $item['jumlah'], $item['harga']);
            $stmt_item->execute();
        }
    }

    echo json_encode(['success' => true, 'redirect' => 'payment_processed.php']);
} else {
    echo json_encode(['success' => false, 'message' => 'Gagal menyimpan pesanan']);
}
?>
