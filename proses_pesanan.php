<?php
session_start();
require 'db_connect.php';

// Validasi login
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

// Validasi data POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['submit_order'])) {
    $_SESSION['error'] = "Akses tidak valid";
    header("Location: pesanan.php");
    exit();
}

// Proses penyimpanan pesanan
try {
    $conn->begin_transaction();
    
    // 1. Buat record pesanan
    $order_ref = 'ORD-'.date('YmdHis').'-'.substr(uniqid(), -6);
    $stmt = $conn->prepare("INSERT INTO pesanan (user_id, order_ref, nama_penerima, telepon, alamat, total, status) 
                          VALUES (?, ?, ?, ?, ?, ?, 'pending')");
    $stmt->bind_param("issssd", 
        $_SESSION['id'],
        $order_ref,
        $_POST['nama_penerima'],
        $_POST['nomor_telepon'],
        $_POST['alamat_pengiriman'],
        $_POST['total_harga']
    );
    $stmt->execute();
    $order_id = $conn->insert_id;
    
    // 2. Simpan item pesanan
    foreach ($_POST['items'] as $item) {
        $stmt = $conn->prepare("INSERT INTO pesanan_items (pesanan_id, produk_id, nama_produk, jumlah, harga) 
                              VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issid", 
            $order_id,
            $item['produk_id'],
            $item['nama_produk'],
            $item['jumlah'],
            $item['harga_satuan']
        );
        $stmt->execute();
    }
    
    $conn->commit();
    
    // 3. Simpan data di session
    $_SESSION['order_details'] = [
        'order_ref' => $order_ref,
        'nama_penerima' => $nama_penerima,
        'nomor_telepon' => $nomor_telepon,
        'alamat_pengiriman' => $alamat_pengiriman,
        'total_harga' => $total_harga,
        'items' => $items
    ];
    
    // 4. Redirect ke pembayaran
    header("Location: pembayaran.php");
    exit();
    
} catch (Exception $e) {
    $conn->rollback();
    $_SESSION['error'] = "Gagal memproses pesanan: ".$e->getMessage();
    header("Location: pesanan.php");
    exit();
}