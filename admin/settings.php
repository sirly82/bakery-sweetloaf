<?php
// admin/settings.php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once 'includes/admin_auth.php'; // Memastikan hanya admin yang bisa mengakses
require_once '../db_connect.php';       // Koneksi ke database

// Ambil username admin dari sesi
$current_admin_username = $_SESSION['admin_username'] ?? 'Admin';

$message = '';
$error = '';

// --- Logika untuk Memperbarui Pengaturan ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_settings'])) {
    $settings_to_update = [
        'bakery_name' => $_POST['bakery_name'] ?? '',
        'bakery_address' => $_POST['bakery_address'] ?? '',
        'bakery_phone' => $_POST['bakery_phone'] ?? '',
        'bakery_email' => $_POST['bakery_email'] ?? '',
        'operating_hours' => $_POST['operating_hours'] ?? '',
        'delivery_fee' => str_replace('.', '', $_POST['delivery_fee'] ?? '0'), // Hapus titik format angka
        'min_order_for_delivery' => str_replace('.', '', $_POST['min_order_for_delivery'] ?? '0') // Hapus titik format angka
    ];

    $all_updated = true;
    foreach ($settings_to_update as $key => $value) {
        // Cek apakah setting_key sudah ada
        $stmt_check = $conn->prepare("SELECT id FROM bakery_settings WHERE setting_key = ?");
        $stmt_check->bind_param("s", $key);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();

        if ($result_check->num_rows > 0) {
            // Jika ada, update nilainya
            $stmt_update = $conn->prepare("UPDATE bakery_settings SET setting_value = ?, updated_at = CURRENT_TIMESTAMP WHERE setting_key = ?");
            $stmt_update->bind_param("ss", $value, $key);
            if (!$stmt_update->execute()) {
                $error .= "Gagal memperbarui '{$key}': " . $stmt_update->error . "<br>";
                $all_updated = false;
            }
            $stmt_update->close();
        } else {
            // Jika belum ada, masukkan sebagai pengaturan baru
            $stmt_insert = $conn->prepare("INSERT INTO bakery_settings (setting_key, setting_value, description) VALUES (?, ?, ?)");
            // Deskripsi bisa disesuaikan atau diabaikan jika tidak diperlukan untuk pengaturan baru
            $description = ucfirst(str_replace('_', ' ', $key)); // Contoh deskripsi otomatis
            $stmt_insert->bind_param("sss", $key, $value, $description);
            if (!$stmt_insert->execute()) {
                $error .= "Gagal menambahkan '{$key}': " . $stmt_insert->error . "<br>";
                $all_updated = false;
            }
            $stmt_insert->close();
        }
        $stmt_check->close();
    }

    if ($all_updated && empty($error)) {
        $message = "Pengaturan berhasil diperbarui.";
    } elseif (!empty($error)) {
        // Error sudah diisi di dalam loop
    } else {
        $error = "Terjadi kesalahan yang tidak diketahui saat memperbarui pengaturan.";
    }
}

// --- Ambil Pengaturan Saat Ini dari Database ---
$settings = [];
$sql_settings = "SELECT setting_key, setting_value FROM bakery_settings";
$result_settings = $conn->query($sql_settings);
if ($result_settings && $result_settings->num_rows > 0) {
    while ($row = $result_settings->fetch_assoc()) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan - SweetLoaf Bakery Admin</title>
    <link rel="stylesheet" href="admin_style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Styling khusus untuk halaman pengaturan */
        .settings-form-card {
            background-color: var(--white);
            padding: 25px;
            border-radius: var(--border-radius-medium);
            box-shadow: var(--shadow-light);
            margin-top: 20px;
        }

        .settings-form-card .form-group {
            margin-bottom: 20px;
        }

        .settings-form-card label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--dark-grey);
        }

        .settings-form-card input[type="text"],
        .settings-form-card input[type="email"],
        .settings-form-card input[type="number"],
        .settings-form-card textarea {
            width: calc(100% - 22px); /* Padding kiri kanan */
            padding: 10px;
            border: 1px solid var(--medium-grey);
            border-radius: var(--border-radius-small);
            font-size: 1em;
            box-sizing: border-box; /* Pastikan padding tidak menambah lebar */
        }

        .settings-form-card textarea {
            min-height: 80px;
            resize: vertical;
        }

        .settings-form-card button[type="submit"] {
            display: block;
            width: 100%;
            padding: 12px;
            background-color: var(--primary-orange);
            color: var(--white);
            border: none;
            border-radius: var(--border-radius-small);
            font-size: 1.1em;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.2s, transform 0.2s;
        }

        .settings-form-card button[type="submit"]:hover {
            background-color: #e67e00; /* Sedikit lebih gelap */
            transform: translateY(-2px);
            box-shadow: var(--shadow-medium);
        }

        /* Alert styling */
        .alert {
            padding: 15px;
            margin-bottom: 25px;
            border: 1px solid transparent;
            border-radius: var(--border-radius-small);
            font-weight: 500;
            text-align: center;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border-color: #c3e6cb;
        }

        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border-color: #f5c6cb;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <?php require_once 'includes/admin_header.php'; ?>
        <?php require_once 'includes/admin_sidebar.php'; ?>

        <main class="admin-main-content">
            <section class="settings-section">
                <h2><i class="fas fa-cogs"></i> Pengaturan Bakery</h2>

                <?php if (!empty($message)): ?>
                    <div class="alert alert-success"><?php echo $message; ?></div>
                <?php endif; ?>
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <div class="settings-form-card">
                    <form action="settings.php" method="post">
                        <div class="form-group">
                            <label for="bakery_name">Nama Bakery:</label>
                            <input type="text" id="bakery_name" name="bakery_name" value="<?php echo htmlspecialchars($settings['bakery_name'] ?? ''); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="bakery_address">Alamat Bakery:</label>
                            <textarea id="bakery_address" name="bakery_address" rows="3"><?php echo htmlspecialchars($settings['bakery_address'] ?? ''); ?></textarea>
                        </div>

                        <div class="form-group">
                            <label for="bakery_phone">Telepon Bakery:</label>
                            <input type="text" id="bakery_phone" name="bakery_phone" value="<?php echo htmlspecialchars($settings['bakery_phone'] ?? ''); ?>">
                        </div>

                        <div class="form-group">
                            <label for="bakery_email">Email Bakery:</label>
                            <input type="email" id="bakery_email" name="bakery_email" value="<?php echo htmlspecialchars($settings['bakery_email'] ?? ''); ?>">
                        </div>

                        <div class="form-group">
                            <label for="operating_hours">Jam Operasional:</label>
                            <input type="text" id="operating_hours" name="operating_hours" value="<?php echo htmlspecialchars($settings['operating_hours'] ?? ''); ?>">
                        </div>

                        <div class="form-group">
                            <label for="delivery_fee">Biaya Pengiriman (Rp):</label>
                            <input type="text" id="delivery_fee" name="delivery_fee" value="<?php echo number_format($settings['delivery_fee'] ?? 0, 0, ',', '.'); ?>" pattern="[0-9.]+" title="Hanya angka dan titik" onkeyup="formatRupiah(this)">
                        </div>

                        <div class="form-group">
                            <label for="min_order_for_delivery">Min. Order Pengiriman (Rp):</label>
                            <input type="text" id="min_order_for_delivery" name="min_order_for_delivery" value="<?php echo number_format($settings['min_order_for_delivery'] ?? 0, 0, ',', '.'); ?>" pattern="[0-9.]+" title="Hanya angka dan titik" onkeyup="formatRupiah(this)">
                        </div>

                        <button type="submit" name="update_settings">Simpan Pengaturan</button>
                    </form>
                </div>
            </section>
        </main>

        <?php require_once 'includes/admin_footer.php'; ?>
    </div>
    <script src="admin.js"></script>
    <script>
        // Fungsi untuk format input menjadi format Rupiah
        function formatRupiah(angka, prefix) {
            var number_string = angka.value.replace(/[^,\d]/g, '').toString(),
                split = number_string.split(','),
                sisa = split[0].length % 3,
                rupiah = split[0].substr(0, sisa),
                ribuan = split[0].substr(sisa).match(/\d{3}/gi);

            if (ribuan) {
                separator = sisa ? '.' : '';
                rupiah += separator + ribuan.join('.');
            }

            rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
            angka.value = prefix == undefined ? rupiah : (rupiah ? 'Rp. ' + rupiah : '');
        }

        // Terapkan format saat halaman dimuat untuk nilai yang sudah ada
        document.addEventListener('DOMContentLoaded', function() {
            var deliveryFeeInput = document.getElementById('delivery_fee');
            var minOrderInput = document.getElementById('min_order_for_delivery');

            if (deliveryFeeInput) {
                formatRupiah(deliveryFeeInput);
            }
            if (minOrderInput) {
                formatRupiah(minOrderInput);
            }
        });
    </script>
</body>
</html>