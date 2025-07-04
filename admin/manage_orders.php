<?php
// admin/manage_orders.php

// Pastikan hanya admin yang bisa mengakses halaman ini
require_once 'includes/admin_auth.php';
// Koneksi ke database
require_once '../db_connect.php';

// Inisialisasi variabel pesan
$message = '';
$message_type = '';

// --- Logika untuk Mengubah Status Pesanan ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'update_status') {
    $order_id = filter_var($_POST['order_id'], FILTER_SANITIZE_NUMBER_INT);

    // Validasi status pembayaran dan status pesanan
    $allowed_payment_statuses = ['unpaid', 'paid', 'cancelled', 'refunded'];
    $allowed_order_statuses = ['pending', 'on_progress', 'completed', 'cancelled'];

    $new_payment_status = $_POST['payment_status'] ?? '';
    $new_order_status = $_POST['order_status'] ?? '';

    if (!in_array($new_payment_status, $allowed_payment_statuses) || !in_array($new_order_status, $allowed_order_statuses)) {
        $message = "Status tidak valid.";
        $message_type = "danger";
    } else {
        try {
            $sql_update = "UPDATE pesanan SET payment_status = ?, order_status = ?, updated_at = CURRENT_TIMESTAMP() WHERE order_id = ?";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->bind_param("ssi", $new_payment_status, $new_order_status, $order_id);

            if ($stmt_update->execute()) {
                $message = "Status pesanan ID #" . htmlspecialchars($order_id) . " berhasil diperbarui.";
                $message_type = "success";
            } else {
                $message = "Gagal memperbarui status pesanan: " . $stmt_update->error;
                $message_type = "danger";
            }
            $stmt_update->close();
        } catch (mysqli_sql_exception $e) {
            $message = "Error: " . $e->getMessage();
            $message_type = "danger";
        }
    }
}

// --- Mengambil Daftar Pesanan dari Database ---
$orders = [];
try {
    // Ambil pesanan, urutkan berdasarkan created_at terbaru, status 'Belum Diproses' di atas
    $sql_orders = "SELECT 
        p.order_id, 
        u.name AS nama_pengguna, 
        p.total AS total_harga,
        p.payment_status,
        p.order_status,
        p.created_at, 
        p.updated_at
    FROM pesanan p
    JOIN users u ON p.user_id = u.id
    ORDER BY FIELD(p.payment_status, 'unpaid', 'paid', 'cancelled'), p.created_at DESC";
    $stmt_orders = $conn->prepare($sql_orders);
    $stmt_orders->execute();
    $result_orders = $stmt_orders->get_result();

    if ($result_orders->num_rows > 0) {
        while ($row = $result_orders->fetch_assoc()) {
            // Ambil detail item dari pesanan_items
            $detail_items = [];
            $total_item = 0;

            $sql_items = "
                SELECT pi.jumlah, pr.nama
                FROM pesanan_items pi
                JOIN products pr ON pi.produk_id = pr.id
                WHERE pi.pesanan_id = ?
            ";
            $stmt_items = $conn->prepare($sql_items);
            $stmt_items->bind_param("i", $row['order_id']);
            $stmt_items->execute();
            $result_items = $stmt_items->get_result();

            while ($item = $result_items->fetch_assoc()) {
                $total_item += (int)$item['jumlah'];
                $detail_items[] = $item['nama'] . " (" . $item['jumlah'] . ")";
            }

            $stmt_items->close();

            // Tambahkan data ke array pesanan
            $row['detail_pesanan'] = implode(', ', $detail_items);
            $row['total_item'] = $total_item;

            // Optional: Jika tabel `pesanan` punya kolom `catatan`, ganti ini
            $row['catatan'] = $row['catatan'] ?? '-';

            // Handle null updated_at agar tidak error strtotime(null)
            if (empty($row['updated_at'])) {
                $row['updated_at'] = null;
            }

            $orders[] = $row;
        }
    }
    $stmt_orders->close();
} catch (mysqli_sql_exception $e) {
    $message = "Error saat mengambil data pesanan: " . $e->getMessage();
    $message_type = "danger";
}

$conn->close(); // Tutup koneksi setelah semua operasi database selesai
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pesanan - SweetLoaf Bakery Admin</title>
    <link rel="stylesheet" href="admin_style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="wrapper">
        <?php include_once 'includes/admin_header.php'; // Menyertakan header ?>
        <?php include_once 'includes/admin_sidebar.php'; // Menyertakan sidebar ?>

        <main class="admin-main-content">
            <div class="admin-content-header">
                <h1><i class="fas fa-shopping-cart"></i> Kelola Pesanan</h1>
            </div>

            <?php if (!empty($message)): ?>
                <div class="alert alert-<?php echo $message_type; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID Pesanan</th>
                            <th>Nama Pengguna</th>
                            <th>Detail Pesanan</th>
                            <th>Total Item</th>
                            <th>Total Harga</th>
                            <th>Catatan</th>
                            <th>Waktu Pesan</th>
                            <th>Terakhir Diperbarui</th>
                            <th>Status Pembayaran</th>
                            <th>Status Pesanan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($orders)): ?>
                            <?php foreach ($orders as $order): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($order['order_id']); ?></td>
                                    <td><?php echo htmlspecialchars($order['nama_pengguna']); ?></td>
                                    <td><?php echo htmlspecialchars($order['detail_pesanan']); ?></td>
                                    <td><?php echo htmlspecialchars($order['total_item']); ?></td>
                                    <td>Rp <?php echo number_format($order['total_harga'], 0, ',', '.'); ?></td>
                                    <td><?php echo htmlspecialchars($order['catatan'] ?? '-'); ?></td>
                                    <td><?php echo date('d M Y H:i', strtotime($order['created_at'])); ?></td>
                                    <td><?php echo $order['updated_at'] ? date('d M Y H:i', strtotime($order['updated_at'])) : '-'; ?></td>
                                    <td>
                                        <select class="status-pembayaran" data-id="<?= $order['order_id'] ?>">
                                            <option value="unpaid" <?= $order['payment_status'] == 'unpaid' ? 'selected' : '' ?>>Belum Dibayar</option>
                                            <option value="paid" <?= $order['payment_status'] == 'paid' ? 'selected' : '' ?>>Sudah Dibayar</option>
                                            <option value="cancelled" <?= $order['payment_status'] == 'cancelled' ? 'selected' : '' ?>>Dibatalkan</option>
                                            <option value="refunded" <?= $order['payment_status'] == 'refunded' ? 'selected' : '' ?>>Dikembalikan</option>
                                        </select>
                                    </td>

                                    <td>
                                        <select class="status-pesanan" data-id="<?= $order['order_id'] ?>">
                                            <option value="pending" <?= $order['order_status'] == 'pending' ? 'selected' : '' ?>>Menunggu</option>
                                            <option value="on_progress" <?= $order['order_status'] == 'on_progress' ? 'selected' : '' ?>>Sedang Diproses</option>
                                            <option value="completed" <?= $order['order_status'] == 'completed' ? 'selected' : '' ?>>Selesai</option>
                                            <option value="cancelled" <?= $order['order_status'] == 'cancelled' ? 'selected' : '' ?>>Dibatalkan</option>
                                        </select>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="10">Tidak ada pesanan ditemukan.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </main>

        <?php include_once 'includes/admin_footer.php'; // Menyertakan footer ?>
    </div>

    </body>
    <script src="assets/js/manage_status.js"></script>
</html>