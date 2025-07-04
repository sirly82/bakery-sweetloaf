<?<php>
session_start();
require 'db_connect.php';

// Validasi user login
if (!isset($_SESSION['id'])) {
    header("HTTP/1.1 401 Unauthorized");
    exit();
}

$user_id = $_SESSION['id'];

// 1. Ambil data keranjang user
$stmt = $conn->prepare("SELECT * FROM keranjang WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("HTTP/1.1 400 Bad Request");
    echo json_encode(['error' => 'Keranjang kosong']);
    exit();
}

// 2. Hitung total
$total = 0;
$items = [];
while ($row = $result->fetch_assoc()) {
    // Ambil detail produk
    $product_stmt = $conn->prepare("SELECT nama, harga FROM products WHERE id = ?");
    $product_stmt->bind_param("i", $row['produk_id']);
    $product_stmt->execute();
    $product = $product_stmt->get_result()->fetch_assoc();
    
    $subtotal = $row['jumlah'] * $product['harga'];
    $total += $subtotal;
    
    $items[] = [
        'produk_id' => $row['produk_id'],
        'nama' => $product['nama'],
        'harga' => $product['harga'],
        'jumlah' => $row['jumlah'],
        'subtotal' => $subtotal
    ];
}

// 3. Simpan ke tabel pesanan sementara
$order_ref = uniqid('ORD-');
$stmt = $conn->prepare("INSERT INTO pesanan (user_id, order_ref, total, status) VALUES (?, ?, ?, 'pending')");
$stmt->bind_param("isi", $user_id, $order_ref, $total);
$stmt->execute();
$order_id = $conn->insert_id;

// 4. Simpan item pesanan
foreach ($items as $item) {
    $stmt = $conn->prepare("INSERT INTO pesanan_items (pesanan_id, produk_id, jumlah, harga) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiid", $order_id, $item['produk_id'], $item['jumlah'], $item['harga']);
    $stmt->execute();
}

// 5. Kosongkan keranjang
$stmt = $conn->prepare("DELETE FROM keranjang WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();

// 6. Return data untuk frontend
echo json_encode([
    'success' => true,
    'order_id' => $order_id,
    'order_ref' => $order_ref,
    'redirect_url' => 'payment.php?order_id='.$order_id
]);
</php>