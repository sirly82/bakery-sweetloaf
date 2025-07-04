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
    $new_status = filter_var($_POST['new_status'], FILTER_SANITIZE_STRING);

    // Validasi status yang diterima
    $allowed_statuses = ['Belum Diproses', 'Sedang Diproses', 'Selesai'];
    if (!in_array($new_status, $allowed_statuses)) {
        $message = "Status tidak valid.";
        $message_type = "danger";
    } elseif (!empty($order_id)) {
        try {
            $sql_update = "UPDATE pesanan SET payment_status = ?, updated_at = CURRENT_TIMESTAMP() WHERE order_id = ?";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->bind_param("si", $new_status, $order_id);

            if ($stmt_update->execute()) {
                $message = "Status pesanan ID #" . htmlspecialchars($order_id) . " berhasil diperbarui menjadi '" . htmlspecialchars($new_status) . "'.";
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
    } else {
        $message = "ID Pesanan tidak valid.";
        $message_type = "danger";
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
        p.payment_status AS status,
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
                            <th>Status</th>
                            <th>Waktu Pesan</th>
                            <th>Terakhir Diperbarui</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($orders)): ?>
                            <?php foreach ($orders as $order): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($order['order_id']); ?></td>
                                    <td><?php echo htmlspecialchars($order['nama_pengguna']); ?></td>
                                    <td><?php echo htmlspecialchars($order['pesanan']); ?></td>
                                    <td><?php echo htmlspecialchars($order['status']); ?></td>
                                    <td><?php echo htmlspecialchars($order['total_item']); ?></td>
                                    <td>Rp <?php echo number_format($order['total_harga'], 0, ',', '.'); ?></td>
                                    <td><?php echo htmlspecialchars($order['catatan'] ?? '-'); ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo strtolower(str_replace(' ', '-', $order['status'])); ?>">
                                            <?php echo htmlspecialchars($order['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('d M Y H:i', strtotime($order['created_at'])); ?></td>
                                    <td><?php echo date('d M Y H:i', strtotime($order['updated_at'])); ?></td>
                                    <td class="actions">
                                        <form action="manage_orders.php" method="post" class="status-form">
                                            <input type="hidden" name="action" value="update_status">
                                            <input type="hidden" name="order_id" value="<?php echo htmlspecialchars($order['order_id']); ?>">
                                            <select name="new_status" class="status-select">
                                                <option value="Belum Diproses" <?php echo ($order['status'] == 'Belum Diproses') ? 'selected' : ''; ?>>Belum Diproses</option>
                                                <option value="Sedang Diproses" <?php echo ($order['status'] == 'Sedang Diproses') ? 'selected' : ''; ?>>Sedang Diproses</option>
                                                <option value="Selesai" <?php echo ($order['status'] == 'Selesai') ? 'selected' : ''; ?>>Selesai</option>
                                            </select>
                                            <button type="submit" class="btn-update-status" title="Update Status"><i class="fas fa-sync"></i></button>
                                        </form>
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
</html>