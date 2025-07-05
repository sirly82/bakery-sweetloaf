<?php
session_start();
require_once 'db_connect.php';

function e($value) {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['id'];
$username = $_SESSION['username'];

$message = '';
$error = '';

// Ambil data pengguna saat ini
$stmt = $conn->prepare("SELECT username, name, email, phone, address FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Update data jika form disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($name) || empty($email) || empty($phone) || empty($address)) {
        $error = "Semua kolom harus diisi, kecuali password.";
    } else {
        $sql = "UPDATE users SET name = ?, email = ?, phone = ?, address = ?";
        $params = "ssss";
        $values = [$name, $email, $phone, $address];

        if (!empty($password)) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $sql .= ", password = ?";
            $params .= "s";
            $values[] = $hashed_password;
        }

        $sql .= " WHERE id = ?";
        $params .= "i";
        $values[] = $user_id;

        $stmt = $conn->prepare($sql);
        $stmt->bind_param($params, ...$values);
        if ($stmt->execute()) {
            header("Location: home.php?update=success");
            exit();
        } else {
            $error = "Gagal memperbarui data akun.";
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Akun Saya</title>
    <link rel="stylesheet" href="assets/styles/kelola_akun.css">
</head>
<body>
    <header>
        <img src="assets/SWEETLOAF.png" alt="Logo Bakery">
        <h1>SweetLoaf Bakery</h1>
    </header>
    <nav>
        <ul>
            <li><a href="home.php">Beranda</a></li>
            <li><a href="pesanan.php">Keranjang</a></li>
            <li><a href="history.php">Pesanan</a></li>
            <li><a href="about.php">Tentang Kami</a></li>
            <li><a href="logout.php" class="logout">Logout</a></li>
        </ul>
    </nav>
    <div class="container">
        <h2>Kelola Akun Saya</h2>

        <?php if ($message): ?>
            <div class="success"><?= htmlspecialchars($message) ?></div>
        <?php elseif ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form action="" method="POST">
            <label for="username">Username (tidak bisa diubah)</label>
            <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" readonly>

            <label for="name">Nama Lengkap</label>
            <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>

            <label for="email">Email</label>
            <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>

            <label for="phone">No. Telepon</label>
            <input type="text" name="phone" value="<?= e($user['phone']) ?>" required>

            
            
            <label for="address">Alamat</label>
            <input type="text" name="address" value="<?= e($user['address']) ?>" required>

            <label for="password">Password Baru (opsional)</label>
            <input type="password" name="password" placeholder="Biarkan kosong jika tidak ingin mengubah">

            <div class="button-row">
                <a href="home.php" class="btn-cancel">Batal</a>
                <button type="submit">Simpan Perubahan</button>
            </div>
        </form>
    </div>

    <script src="assets/js/kelola_akun.js"></script>
</body>
</html>
