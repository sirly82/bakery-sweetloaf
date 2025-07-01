<?php
session_start();
require 'db_connect.php'; // Mengubah koneksi.php menjadi db_connect.php

$nameErr = $usernameErr = $passwordErr = $emailErr = $phoneErr = $addressErr = "";
$name = $username = $password = $email = $phone = $address = "";
$successMessage = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    function test_input($data) {
        return htmlspecialchars(stripslashes(trim($data)));
    }

    $isValid = true;

    // Validasi Nama
    if (empty($_POST["nama"])) {
        $nameErr = "Nama harus diisi"; $isValid = false;
    } else {
        $name = test_input($_POST["nama"]);
    }

    // Validasi Username
    if (empty($_POST["username"])) {
        $usernameErr = "Username harus diisi"; $isValid = false;
    } else {
        $username = test_input($_POST["username"]);
        // Cek apakah username sudah ada
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $usernameErr = "Username ini sudah terdaftar.";
            $isValid = false;
        }
        $stmt->close();
    }

    // Validasi Password
    if (empty($_POST["pw"])) {
        $passwordErr = "Password harus diisi"; $isValid = false;
    } elseif (strlen($_POST["pw"]) < 8) {
        $passwordErr = "Password harus lebih dari 8 karakter"; $isValid = false;
    } else {
        $password_raw = $_POST["pw"];
        $password_confirm = $_POST["confirm_password"];
        if ($password_raw !== $password_confirm) {
            $passwordErr .= " Konfirmasi password tidak cocok."; $isValid = false;
        } else {
            $password = password_hash($password_raw, PASSWORD_DEFAULT);
        }
    }

    // Validasi Email
    if (empty($_POST["email"])) {
        $emailErr = "Email harus diisi"; $isValid = false;
    } elseif (!filter_var($_POST["email"], FILTER_VALIDATE_EMAIL)) {
        $emailErr = "Format email tidak valid"; $isValid = false;
    } else {
        $email = test_input($_POST["email"]);
        // Cek apakah email sudah ada
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $emailErr = "Email ini sudah terdaftar.";
            $isValid = false;
        }
        $stmt->close();
    }

    // Validasi Telepon
    if (empty($_POST["telepon"])) {
        $phoneErr = "Nomor telepon harus diisi"; $isValid = false;
    } else {
        $phone = test_input($_POST["telepon"]);
    }

    // Validasi Alamat
    if (empty($_POST["alamat"])) {
        $addressErr = "Alamat harus diisi"; $isValid = false;
    } else {
        $address = test_input($_POST["alamat"]);
    }

    // Default role untuk pendaftaran adalah 'user'
    $role = 'user'; 

    if ($isValid) {
        // Prepare and bind
        $stmt = $conn->prepare("INSERT INTO users (name, username, password, email, phone, address, role) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssss", $name, $username, $password, $email, $phone, $address, $role);

        if ($stmt->execute()) {
            $successMessage = "Registrasi berhasil! Anda dapat masuk sekarang.";
            // Setelah registrasi berhasil, redirect ke halaman login
            header("Location: login.php?registration=success");
            exit();
        } else {
            $nameErr = "Error: " . $stmt->error; // Menggunakan $nameErr untuk menampilkan error database
        }
        $stmt->close();
    }
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Daftar | Bakery</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="register.css"> </head>
<body>
<div class="overlay"></div>

<header>
  <div class="toggle-auth">
    <a href="login.php">Masuk</a>
    <a href="register.php" class="active">Daftar</a>
  </div>
</header>

<div class="register-container">
  <div class="logo">
    <img src="assets/logo.png" alt="SweetLoaf Logo" />
  </div>
  <h3>Buat Akun Baru</h3>

  <?php if (!empty($successMessage)) : ?>
    <div class="success-message"><?php echo $successMessage; ?></div>
  <?php endif; ?>

  <form action="" method="post">
    <div class="form-group">
      <label for="nama">Nama Lengkap</label>
      <input type="text" name="nama" class="form-control" value="<?= htmlspecialchars($name) ?>" placeholder="Nama Lengkap" required>
      <span class="error"><?= $nameErr ?></span>
    </div>
    <div class="form-group">
      <label for="username">Username</label>
      <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($username) ?>" placeholder="Username" required>
      <span class="error"><?= $usernameErr ?></span>
    </div>
    <div class="form-group">
      <label for="email">Email</label>
      <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($email) ?>" placeholder="Email aktif" required>
      <span class="error"><?= $emailErr ?></span>
    </div>
    <div class="form-group">
      <label for="pw">Password</label>
      <input type="password" name="pw" class="form-control" placeholder="Password (min 8 karakter)" required>
      <span class="error"><?= $passwordErr ?></span>
    </div>
    <div class="form-group">
      <label for="confirm_password">Konfirmasi Password</label>
      <input type="password" name="confirm_password" class="form-control" placeholder="Konfirmasi Password" required>
    </div>
    <div class="form-group">
      <label for="telepon">Nomor Telepon</label>
      <input type="text" name="telepon" class="form-control" value="<?= htmlspecialchars($phone) ?>" placeholder="081234567890">
      <span class="error"><?= $phoneErr ?></span>
    </div>
    <div class="form-group">
      <label for="alamat">Alamat</label>
      <textarea name="alamat" class="form-control" placeholder="Alamat lengkap"><?= htmlspecialchars($address) ?></textarea>
      <span class="error"><?= $addressErr ?></span>
    </div>
    <button type="submit" class="btn-primary">Daftar Sekarang</button>
  </form>

  <p class="text-center">Sudah punya akun? <a href="login.php">Masuk disini</a></p>
</div>

<div class="wave-bottom">
  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320">
    <path fill="#fb8a06" fill-opacity="1" d="M0,288L30,250.7C60,213,120,139,180,133.3C240,128,300,192,360,224C420,256,480,256,540,250.7C600,245,660,235,720,208C780,181,840,139,900,138.7C960,139,1020,181,1080,213.3C1140,245,1200,267,1260,261.3C1320,256,1380,224,1410,208L1440,192L1440,320L1410,320C1380,320,1320,320,1260,320C1200,320,1140,320,1080,320C1020,320,960,320,900,320C840,320,780,320,720,320C660,320,600,320,540,320C480,320,420,320,360,320C300,320,240,320,180,320C120,320,60,320,30,320L0,320Z"></path>
  </svg>
</div>
</body>
</html>