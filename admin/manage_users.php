<?php
// admin/manage_users.php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once 'includes/admin_auth.php'; // Memastikan hanya admin yang bisa mengakses
require_once '../db_connect.php';       // Koneksi ke database

$message = '';
$error = '';

// --- Logika Tambah/Edit Pengguna ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $username = trim($_POST['username'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $role = $_POST['role'] ?? 'user'; // Default role
    $password = $_POST['password'] ?? '';
    $user_id = $_POST['user_id'] ?? null;

    // Validasi dasar
    if (empty($username) || empty($name) || empty($role)) {
        $error = "Username, nama, dan peran harus diisi.";
    } elseif ($action == 'add' && empty($password)) {
        $error = "Password harus diisi untuk pengguna baru.";
    } else {
        if ($action == 'add') {
            // Tambah Pengguna Baru
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (username, email, phone, password, name, role) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssss", $username, $email, $phone, $hashed_password, $name, $role);
            if ($stmt->execute()) {
                $message = "Pengguna baru '{$username}' berhasil ditambahkan.";
            } else {
                if ($stmt->errno == 1062) { // MySQL error code for duplicate entry
                    $error = "Username '{$username}' sudah ada. Harap gunakan username lain.";
                } else {
                    $error = "Gagal menambahkan pengguna: " . $stmt->error;
                }
            }
            $stmt->close();
        } elseif ($action == 'edit' && $user_id) {
            // Edit Pengguna
            $sql = "UPDATE users SET username = ?, name = ?, email = ?, phone = ?, role = ?";
            $params = "sssss";
            $bind_values = [$username, $name, $email, $phone, $role];

            if (!empty($password)) {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $sql .= ", password = ?";
                $params .= "s";
                $bind_values[] = $hashed_password;
            }
            $sql .= " WHERE id = ?";
            $params .= "i";
            $bind_values[] = $user_id;

            $stmt = $conn->prepare($sql);
            if ($stmt) {
                $stmt->bind_param($params, ...$bind_values);
                if ($stmt->execute()) {
                    $message = "Pengguna '{$username}' berhasil diperbarui.";
                } else {
                    if ($stmt->errno == 1062) {
                        $error = "Username '{$username}' sudah ada. Harap gunakan username lain.";
                    } else {
                        $error = "Gagal memperbarui pengguna: " . $stmt->error;
                    }
                }
                $stmt->close();
            } else {
                $error = "Gagal menyiapkan statement: " . $conn->error;
            }
        }
    }
}

// --- Logika Hapus Pengguna ---
if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $user_id_to_delete = $_GET['id'];

    // Cek apakah pengguna yang dihapus adalah admin yang sedang login
    if ($user_id_to_delete == $_SESSION['admin_id']) {
        $error = "Anda tidak bisa menghapus akun Anda sendiri!";
    } else {
        // Cek apakah user memiliki pesanan terlebih dahulu
        $stmt_check = $conn->prepare("SELECT COUNT(*) FROM pesanan WHERE user_id = ?");
        $stmt_check->bind_param("i", $user_id_to_delete);
        $stmt_check->execute();
        $stmt_check->bind_result($pesanan_count);
        $stmt_check->fetch();
        $stmt_check->close();

        if ($pesanan_count > 0) {
            $error = "Gagal menghapus pengguna karena memiliki data pesanan terkait.";
        } else {
            $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
            $stmt->bind_param("i", $user_id_to_delete);
            if ($stmt->execute()) {
                $message = "Pengguna berhasil dihapus.";
            } else {
                $error = "Gagal menghapus pengguna.";
            }
            $stmt->close();
        }
    }
}

// --- Ambil Daftar Semua Pengguna ---
$users = [];
$sql_users = "SELECT id, username, name, role FROM users ORDER BY role ASC, name ASC";
$result_users = $conn->query($sql_users);
if ($result_users && $result_users->num_rows > 0) {
    while ($row = $result_users->fetch_assoc()) {
        $users[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Akun Admin & User - SweetLoaf Bakery Admin</title>
    <link rel="stylesheet" href="assets/styles/admin_style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Styling khusus untuk halaman manajemen pengguna */
        .user-form-card, .user-list-card {
            background-color: var(--white);
            padding: 25px;
            border-radius: var(--border-radius-medium);
            box-shadow: var(--shadow-light);
            margin-bottom: 20px;
        }

        .user-form-card .form-group {
            margin-bottom: 15px;
        }

        .user-form-card label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: var(--dark-grey);
        }

        .user-form-card input[type="text"],
        .user-form-card input[type="password"],
        .user-form-card select {
            width: calc(100% - 22px);
            padding: 10px;
            border: 1px solid var(--medium-grey);
            border-radius: var(--border-radius-small);
            font-size: 1em;
            box-sizing: border-box;
        }

        .user-form-card button[type="submit"] {
            padding: 12px 20px;
            background-color: var(--primary-orange);
            color: var(--white);
            border: none;
            border-radius: var(--border-radius-small);
            font-size: 1.1em;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.2s, transform 0.2s;
            width: auto;
            min-width: 150px;
        }

        .user-form-card button[type="submit"]:hover {
            background-color: #e67e00;
            transform: translateY(-2px);
            box-shadow: var(--shadow-medium);
        }

        .user-list-card table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        .user-list-card th,
        .user-list-card td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }

        .user-list-card th {
            background-color: var(--light-grey);
            font-weight: 600;
            color: var(--dark-grey);
        }

        .user-actions {
            display: flex;
            gap: 5px;
        }

        .user-actions .btn-edit,
        .user-actions .btn-delete {
            padding: 6px 10px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.9em;
            text-decoration: none;
            color: white;
            transition: background-color 0.2s;
        }

        .user-actions .btn-edit {
            background-color: #007bff; /* Biru */
        }
        .user-actions .btn-edit:hover {
            background-color: #0056b3;
        }

        .user-actions .btn-delete {
            background-color: #dc3545; /* Merah */
        }
        .user-actions .btn-delete:hover {
            background-color: #c82333;
        }

        .no-data-message {
            text-align: center;
            color: #666;
            padding: 20px;
            background-color: #f9f9f9;
            border-radius: var(--border-radius-small);
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <?php require_once 'includes/admin_header.php'; ?>
        <?php require_once 'includes/admin_sidebar.php'; ?>

        <main class="admin-main-content">
            <section class="manage-users-section">
                <h2><i class="fas fa-users-cog"></i> Kelola Akun Admin & User</h2>

                <?php if (!empty($message)): ?>
                    <div class="alert alert-success"><?php echo $message; ?></div>
                <?php endif; ?>
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <div class="user-form-card">
                    <h3><?php echo isset($_GET['edit_id']) ? 'Edit Pengguna' : 'Tambah Pengguna Baru'; ?></h3>
                    <?php
                    $edit_user = null;
                    if (isset($_GET['edit_id'])) {
                        $edit_id = $_GET['edit_id'];
                        $stmt_edit = $conn->prepare("SELECT id, username, name, email, phone, role FROM users WHERE id = ?");
                        $stmt_edit->bind_param("i", $edit_id);
                        $stmt_edit->execute();
                        $result_edit = $stmt_edit->get_result();
                        if ($result_edit->num_rows > 0) {
                            $edit_user = $result_edit->fetch_assoc();
                        }
                        $stmt_edit->close();
                    }
                    ?>
                    <form action="manage_users.php" method="post">
                        <input type="hidden" name="action" value="<?php echo isset($edit_user) ? 'edit' : 'add'; ?>">
                        <?php if (isset($edit_user)): ?>
                            <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($edit_user['id']); ?>">
                        <?php endif; ?>

                        <div class="form-group">
                            <label for="username">Username:</label>
                            <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($edit_user['username'] ?? ''); ?>" required <?php echo isset($edit_user) ? 'readonly' : ''; // Username tidak bisa diubah saat edit ?>>
                            <?php if (isset($edit_user)): ?>
                                <small>Username tidak bisa diubah.</small>
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <label for="email">Email:</label>
                            <input type="text" id="email" name="email" value="<?php echo htmlspecialchars($edit_user['email'] ?? ''); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="phone">No. Telepon:</label>
                            <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($edit_user['phone'] ?? ''); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="name">Nama Lengkap:</label>
                            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($edit_user['name'] ?? ''); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="role">Peran (Role):</label>
                            <select id="role" name="role" required>
                                <option value="admin" <?php echo (isset($edit_user) && $edit_user['role'] == 'admin') ? 'selected' : ''; ?>>Admin</option>
                                <option value="user" <?php echo (isset($edit_user) && $edit_user['role'] == 'user') ? 'selected' : ''; ?>>User/Karyawan</option>
                                </select>
                        </div>

                        <div class="form-group">
                            <label for="password">Password <?php echo isset($edit_user) ? '(Biarkan kosong jika tidak ingin mengubah)' : '*'; ?>:</label>
                            <input type="password" id="password" name="password" placeholder="<?php echo isset($edit_user) ? '*****' : 'Masukkan password'; ?>" <?php echo isset($edit_user) ? '' : 'required'; ?>>
                        </div>

                        <button type="submit"><?php echo isset($edit_user) ? 'Update Pengguna' : 'Tambah Pengguna'; ?></button>
                    </form>
                </div>

                <div class="user-list-card">
                    <h3>Daftar Pengguna</h3>
                    <?php if (!empty($users)): ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Username</th>
                                    <th>Nama</th>
                                    <th>Peran</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($user['id']); ?></td>
                                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                                        <td><?php echo htmlspecialchars($user['name']); ?></td>
                                        <td><?php echo htmlspecialchars(ucfirst($user['role'])); ?></td>
                                        <td class="user-actions">
                                            <a href="manage_users.php?edit_id=<?php echo htmlspecialchars($user['id']); ?>" class="btn-edit"><i class="fas fa-edit"></i> Edit</a>
                                            <?php if ($user['id'] != $_SESSION['admin_id']): // Tidak bisa menghapus akun sendiri ?>
                                                <a href="manage_users.php?action=delete&id=<?php echo htmlspecialchars($user['id']); ?>" class="btn-delete" onclick="return confirm('Apakah Anda yakin ingin menghapus pengguna ini? Tindakan ini tidak dapat dibatalkan.');"><i class="fas fa-trash-alt"></i> Hapus</a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p class="no-data-message">Tidak ada pengguna terdaftar.</p>
                    <?php endif; ?>
                </div>

            </section>
        </main>

        <?php require_once 'includes/admin_footer.php'; ?>
    </div>
    <script src="assets/js/admin.js"></script>
    <?php $conn->close(); ?>
</body>
</html>