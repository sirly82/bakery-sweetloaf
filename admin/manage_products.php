<?php
// admin/manage_products.php

// Pastikan hanya admin yang bisa mengakses halaman ini
require_once 'includes/admin_auth.php';
// Koneksi ke database
require_once '../db_connect.php';

// Inisialisasi variabel pesan
$message = '';
$message_type = '';

// --- Logika Penanganan Form (Tambah/Edit/Hapus Produk) ---
// Bagian ini akan kita isi nanti setelah kerangka dasar terbentuk.
// Untuk saat ini, kita fokus menampilkan daftar produk.

// --- Mengambil Daftar Produk dari Database ---
$products = [];
try {
    $sql = "SELECT id, foto, nama, deskripsi, harga, stok FROM products ORDER BY id DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
    }
    $stmt->close();
} catch (mysqli_sql_exception $e) {
    $message = "Error: " . $e->getMessage();
    $message_type = "danger";
}

$conn->close(); // Tutup koneksi setelah semua operasi database selesai
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Produk - SweetLoaf Bakery Admin</title>
    <link rel="stylesheet" href="admin_style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="wrapper">
        <?php include_once 'includes/admin_header.php'; // Menyertakan header ?>
        <?php include_once 'includes/admin_sidebar.php'; // Menyertakan sidebar ?>

        <main class="admin-main-content">
            <div class="admin-content-header">
                <h1><i class="fas fa-boxes"></i> Kelola Produk</h1>
                <button class="btn-primary" id="addProductBtn"><i class="fas fa-plus"></i> Tambah Produk Baru</button>
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
                            <th>ID</th>
                            <th>Foto</th>
                            <th>Nama Produk</th>
                            <th>Deskripsi</th>
                            <th>Harga</th>
                            <th>Stok</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($products)): ?>
                            <?php foreach ($products as $product): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($product['id']); ?></td>
                                    <td>
                                        <?php if (!empty($product['foto']) && file_exists('../assets/' . $product['foto'])): ?>
                                            <img src="../assets/<?php echo htmlspecialchars($product['foto']); ?>" alt="<?php echo htmlspecialchars($product['nama']); ?>" class="product-thumb">
                                        <?php else: ?>
                                            Tidak Ada Foto
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($product['nama']); ?></td>
                                    <td><?php echo htmlspecialchars(mb_strimwidth($product['deskripsi'], 0, 70, "...")); ?></td> <td>Rp <?php echo number_format($product['harga'], 0, ',', '.'); ?></td>
                                    <td><?php echo htmlspecialchars($product['stok']); ?></td>
                                    <td class="actions">
                                        <button class="btn-edit" data-id="<?php echo $product['id']; ?>"><i class="fas fa-edit"></i> Edit</button>
                                        <button class="btn-delete" data-id="<?php echo $product['id']; ?>"><i class="fas fa-trash-alt"></i> Hapus</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7">Tidak ada produk ditemukan.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div id="productModal" class="modal">
                <div class="modal-content">
                    <span class="close-button">&times;</span>
                    <h2>Tambah/Edit Produk</h2>
                    <form id="productForm" action="manage_products.php" method="post" enctype="multipart/form-data">
                        <input type="hidden" name="product_id" id="product_id">
                        <div class="form-group">
                            <label for="nama">Nama Produk:</label>
                            <input type="text" id="nama" name="nama" required>
                        </div>
                        <div class="form-group">
                            <label for="deskripsi">Deskripsi:</label>
                            <textarea id="deskripsi" name="deskripsi"></textarea>
                        </div>
                        <div class="form-group">
                            <label for="harga">Harga:</label>
                            <input type="number" id="harga" name="harga" required min="0">
                        </div>
                        <div class="form-group">
                            <label for="stok">Stok:</label>
                            <input type="number" id="stok" name="stok" required min="0">
                        </div>
                        <div class="form-group">
                            <label for="foto">Foto Produk:</label>
                            <input type="file" id="foto" name="foto" accept="image/*">
                            <input type="hidden" name="current_foto" id="current_foto">
                            <div id="fotoPreview" style="margin-top: 10px;"></div>
                        </div>
                        <button type="submit" name="submit_product" class="btn-primary">Simpan Produk</button>
                    </form>
                </div>
            </div>

        </main>

        <?php include_once 'includes/admin_footer.php'; // Menyertakan footer ?>
    </div>
</body>
</html>