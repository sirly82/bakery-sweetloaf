<?php
session_start();
require 'db_connect.php';

header('Content-Type: application/json');

// Validasi sesi
if (!isset($_SESSION['id']) || !isset($_SESSION['order_details'])) {
    echo json_encode(['success' => false, 'message' => 'Session tidak valid']);
    exit;
}

// Validasi JSON
$rawData = file_get_contents("php://input");
$data = json_decode($rawData, true);

if ($data !== null && isset($data['finish']) && $data['finish'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Data tidak valid']);
    exit;
}

$user_id = $_SESSION['id'];
$order = $_SESSION['order_details'];

// Data pesanan
$order_ref = 'ORD' . strtoupper(uniqid());
$total = $order['total_harga'];
$payment_type = 'qris';
$payment_status = 'unpaid';          // Belum dibayar
$order_status = 'on_progress';       // Pesanan baru dibuat
$created_at = date('Y-m-d H:i:s');
$paid_at = null;                     // Belum dibayar

// Simpan ke tabel pesanan (perhatikan kolom bertambah)
$sql = "INSERT INTO pesanan (user_id, order_ref, total, payment_type, payment_status, order_status, created_at, paid_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Query gagal: ' . $conn->error]);
    exit;
}

$stmt->bind_param("isdsssss", $user_id, $order_ref, $total, $payment_type, $payment_status, $order_status, $created_at, $paid_at);

if ($stmt->execute()) {
    $pesanan_id = $stmt->insert_id;

    // Simpan item
    if (isset($order['items']) && is_array($order['items'])) {
        $stmt_item = $conn->prepare("INSERT INTO pesanan_items (pesanan_id, produk_id, jumlah, harga) VALUES (?, ?, ?, ?)");
        foreach ($order['items'] as $item) {
            $stmt_item->bind_param("iiid", $pesanan_id, $item['produk_id'], $item['jumlah'], $item['harga']);
            $stmt_item->execute();
        }
    }

    // Bersihkan session
    unset($_SESSION['order_details']);
    $_SESSION['checkout_completed'] = true;
    $_SESSION['last_order_id'] = $pesanan_id;

    echo json_encode(['success' => true, 'redirect' => 'payment_processed.php']);
} else {
    echo json_encode(['success' => false, 'message' => 'Gagal menyimpan pesanan: ' . $stmt->error]);
}
?>