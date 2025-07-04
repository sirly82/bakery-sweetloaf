<?php
session_start();
header('Content-Type: application/json');

// Validasi keranjang belanja
if (empty($_SESSION['cart'])) {
    echo json_encode(['success' => false, 'message' => 'Keranjang belanja kosong']);
    exit;
}

// Simpan data untuk cetak
$_SESSION['print_data'] = [
    'cart' => $_SESSION['cart'],
    'print_time' => date('Y-m-d H:i:s'),
    'transaction_id' => 'TRX-' . substr(uniqid(), -6)
];

echo json_encode(['success' => true]);
?>