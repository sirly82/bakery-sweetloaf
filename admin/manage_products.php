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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_product'])) {
    $id = $_POST['product_id'] ?? '';
    $nama = trim($_POST['nama']);
    $deskripsi = trim($_POST['deskripsi']);
    $harga = intval($_POST['harga']);
    $stok = intval($_POST['stok']);
    $current_foto = $_POST['current_foto'] ?? '';
    $foto = '';

    // Proses upload foto
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        $foto = basename($_FILES['foto']['name']);
        $upload_dir = "../assets/uploads/products/";

        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        if (move_uploaded_file($_FILES['foto']['tmp_name'], $upload_dir . $foto)) {
            // Upload sukses, pakai nama file baru
        } else {
            // Upload gagal, pakai current_foto
            $foto = $current_foto;
        }
    } else {
        // Tidak upload baru, pakai foto lama
        $foto = $current_foto;
    }

    if (!empty($id)) {
        // Edit produk
        $sql = "UPDATE products SET nama = ?, deskripsi = ?, harga = ?, stok = ?, foto = ?, updated_at = NOW() WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssdisi", $nama, $deskripsi, $harga, $stok, $foto, $id);
        if ($stmt->execute()) {
            $message = "Produk berhasil diperbarui.";
            $message_type = "success";
        } else {
            $message = "Gagal memperbarui produk: " . $stmt->error;
            $message_type = "danger";
        }
        $stmt->close();
    } else {
        // Tambah produk baru
        $sql = "INSERT INTO products (nama, deskripsi, harga, stok, foto) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssdis", $nama, $deskripsi, $harga, $stok, $foto);
        if ($stmt->execute()) {
            $message = "Produk baru berhasil ditambahkan.";
            $message_type = "success";
        } else {
            $message = "Gagal menambahkan produk: " . $stmt->error;
            $message_type = "danger";
        }
        $stmt->close();
    }
}

// --- Hapus Produk ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_product'])) {
    $product_id = $_POST['delete_product'];
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    if ($stmt->execute()) {
        $message = "Produk berhasil dihapus.";
        $message_type = "success";
    } else {
        $message = "Gagal menghapus produk: " . $stmt->error;
        $message_type = "danger";
    }
    $stmt->close();
}

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
    <link rel="stylesheet" href="assets/styles/admin_style.css">
    <link rel="stylesheet" href="assets/styles/manage_products.css">
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
                                        <?php 
                                            $foto_path = 'assets/uploads/products/' . $product['foto'];
                                        ?>
                                        <?php if (!empty($product['foto']) && file_exists($foto_path)): ?>
                                            <img src="<?php echo $foto_path; ?>" alt="<?php echo htmlspecialchars($product['nama']); ?>" class="product-thumb">
                                        <?php else: ?>
                                            Tidak Ada Foto
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($product['nama']); ?></td>
                                    <td><?php echo htmlspecialchars(mb_strimwidth($product['deskripsi'], 0, 70, "...")); ?></td> <td>Rp <?php echo number_format($product['harga'], 0, ',', '.'); ?></td>
                                    <td><?php echo htmlspecialchars($product['stok']); ?></td>
                                    <td class="actions">
                                        <button class="btn-edit"
                                                data-id="<?php echo $product['id']; ?>"
                                                data-nama="<?php echo htmlspecialchars($product['nama']); ?>"
                                                data-deskripsi="<?php echo htmlspecialchars($product['deskripsi']); ?>"
                                                data-harga="<?php echo $product['harga']; ?>"
                                                data-stok="<?php echo $product['stok']; ?>"
                                                data-foto="<?php echo htmlspecialchars($product['foto']); ?>">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        <form method="post" style="display:inline;">
                                            <input type="hidden" name="delete_product" value="<?php echo $product['id']; ?>">
                                            <button type="submit" class="btn-delete" onclick="return confirm('Yakin ingin menghapus produk ini?');">
                                                <i class="fas fa-trash-alt"></i> Hapus
                                            </button>
                                        </form>
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
    <script src="assets/js/manage_products.js"></script>
</body>
</html>