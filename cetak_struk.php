<?php
session_start();

// Pastikan data print tersedia
if (!isset($_SESSION['print_data'])) {
    header('Location: home.php');
    exit;
}

$printData = $_SESSION['print_data'];
$cartItems = $printData['cart'];
$total = array_reduce($cartItems, function($sum, $item) {
    return $sum + ($item['price'] * $item['quantity']);
}, 0);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Struk</title>
    <style>
        /* CSS Struk dari sebelumnya */
    </style>
</head>
<body>
    <div class="struk-container" id="strukContainer">
        <!-- Isi struk -->
    </div>
    
    <div class="button-container">
        <button id="goHomeBtn">Kembali</button>
        <button onclick="window.print()">Cetak</button>
    </div>
    
    <script src="script.js"></script>
</body>
</html>