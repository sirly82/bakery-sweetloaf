<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: text/plain');

require '../db_connect.php';

// Log semua POST yang masuk
file_put_contents(__DIR__ . '/debug_log.txt', date('Y-m-d H:i:s') . ' - ' . json_encode($_POST) . PHP_EOL, FILE_APPEND);

$order_id = $_POST['id'] ?? null;

if (!$order_id) {
    http_response_code(400);
    echo "ID pesanan tidak dikirim.";
    exit;
}

// Update Payment Status
if (isset($_POST['payment_status'])) {
    $status = $_POST['payment_status'];

    if ($status === 'paid') {
        // Jika dibayar, update juga paid_at
        $stmt = $conn->prepare("UPDATE pesanan SET payment_status = ?, paid_at = NOW(), updated_at = NOW() WHERE order_id = ?");
    } else {
        // Jika bukan 'paid', kosongkan paid_at
        $stmt = $conn->prepare("UPDATE pesanan SET payment_status = ?, paid_at = NULL, updated_at = NOW() WHERE order_id = ?");
    }

    if ($stmt === false) {
        http_response_code(500);
        echo "Prepare gagal: " . $conn->error;
        exit;
    }

    $stmt->bind_param("si", $status, $order_id);
    if ($stmt->execute()) {
        echo "OK - payment_status updated";
    } else {
        http_response_code(500);
        echo "Execute gagal: " . $stmt->error;
    }
    $stmt->close();
    exit;
}

// Update Order Status
if (isset($_POST['order_status'])) {
    $status = $_POST['order_status'];
    $stmt = $conn->prepare("UPDATE pesanan SET order_status = ?, updated_at = NOW() WHERE order_id = ?");
    $stmt->bind_param("si", $status, $order_id);
    if ($stmt->execute()) {
        echo "OK - order_status updated";
    } else {
        http_response_code(500);
        echo "Gagal update order_status: " . $stmt->error;
    }
    $stmt->close();
    exit;
}

http_response_code(400);
echo "Tidak ada data status yang dikirim.";
