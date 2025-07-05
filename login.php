<?php
session_start();
require 'db_connect.php'; // Mengubah koneksi.php menjadi db_connect.php

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $username = $_POST['username']; // Menggunakan username
  $password = $_POST['password'];

  $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?"); // Menggunakan username
  $stmt->bind_param("s", $username);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    if (password_verify($password, $user['password'])) {
      $_SESSION['username'] = $username;
      $_SESSION['role'] = $user['role'];
      $_SESSION['id'] = $user['id']; // Penting untuk melacak ID pengguna
      $_SESSION['nama'] = $user['nama']; // Penting untuk nama pengguna
      
      // Redirect berdasarkan role
      if ($user['role'] == 'admin') {
        header("Location: product.php"); // Pastikan file product.php ada untuk admin
      } else {
        header("Location: home.php"); // Diarahkan ke home.php untuk pengguna biasa
      }
      exit();
    } else {
      $error = "Password salah.";
    }
  } else {
    $error = "Username tidak ditemukan.";
  }

  $stmt->close();
  $conn->close();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Login | Bakery</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    body {
      margin: 0;
      font-family: 'Poppins', sans-serif;
      /* Background image sudah ada di register.css yang terhubung */
      background-image: url('assets/images/bg-login.png'); /* Pastikan path ini benar jika tidak dari CSS */
      background-size: cover;
      background-position: center;
      background-repeat: no-repeat;
      display: flex;
      align-items: center;
      justify-content: center;
      min-height: 100vh;
      position: relative;
      overflow: hidden;
    }

    .overlay {
      position: absolute;
      top: 0; left: 0;
      width: 100%; height: 100%;
      background-color: rgba(255, 255, 255, 0.4);
      z-index: 0;
    }

    header {
      position: absolute;
      top: 24px;
      right: 24px;
      z-index: 10;
    }

    header .toggle-auth {
      display: flex;
      border-radius: 9999px;
      overflow: hidden;
      background: #111;
    }

    header .toggle-auth a {
      padding: 10px 20px;
      color: white;
      font-weight: bold;
      text-decoration: none;
    }

    header .toggle-auth a.active {
      background: #fb8a06;
    }

    .login-container {
      position: relative;
      z-index: 2;
      background: rgba(255, 255, 255, 0.2);
      backdrop-filter: blur(15px);
      -webkit-backdrop-filter: blur(15px);
      border-radius: 20px;
      padding: 50px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
      width: 100%;
      max-width: 400px;
      text-align: center;
    }

    .login-container .logo img {
      max-width: 150px;
      margin-bottom: 20px;
    }

    .login-container h3 {
      color: #333;
      margin-bottom: 30px;
    }

    .form-label {
      display: block;
      text-align: left;
      margin-bottom: 8px;
      font-weight: 500;
      color: #555;
    }

    .form-control {
      width: calc(100% - 24px); /* Mengurangi padding */
      padding: 12px;
      margin-bottom: 20px;
      border: 1px solid rgba(255, 255, 255, 0.3);
      background-color: rgba(255, 255, 255, 0.2);
      border-radius: 12px;
      color: #000;
    }

    .form-control::placeholder {
      color: #999;
    }

    .form-control:focus {
      outline: none;
      border-color: #3b82f6;
      background-color: rgba(255, 255, 255, 0.3);
      box-shadow: 0 0 0 0.25rem rgba(59, 130, 246, 0.25);
    }

    .btn-primary {
      background-color: #fb8a06;
      border: none;
      padding: 12px;
      border-radius: 12px;
      font-weight: bold;
      font-size: 16px;
      width: 100%;
      color: white;
      cursor: pointer;
      transition: background-color 0.3s;
    }

    .btn-primary:hover {
      background-color: #f97316;
    }

    .text-center {
      text-align: center;
      margin-top: 20px;
      color: #555;
    }

    .text-center a {
      color: #fb8a06;
      text-decoration: none;
      font-weight: bold;
    }

    .text-center a:hover {
      text-decoration: underline;
    }

    .alert {
      background-color: #f8d7da;
      color: #721c24;
      border: 1px solid #f5c6cb;
      padding: 10px;
      margin-bottom: 20px;
      border-radius: 5px;
      text-align: left;
    }

    .wave-bottom {
      position: absolute;
      bottom: 0;
      left: 0;
      width: 100%;
      z-index: 1;
      line-height: 0;
    }

    .wave-bottom svg {
      width: 100%;
      height: auto;
      display: block;
    }
  </style>
</head>
<body>
  <div class="overlay"></div>

  <header>
    <div class="toggle-auth">
      <a href="login.php" class="active">Masuk</a>
      <a href="register.php">Daftar</a>
    </div>
  </header>

  <div class="login-container">
    <div class="logo">
      <img src="assets/images/logo.png" alt="SweetLoaf Logo">
    </div>
    <h3>Masuk ke akun Anda</h3>

    <?php if (!empty($error)) : ?>
      <div class="alert"><?php echo $error; ?></div>
    <?php endif; ?>

    <form action="" method="post">
      <label class="form-label" for="username">Username</label> <input type="text" name="username" class="form-control" placeholder="Masukkan username" required /> <label class="form-label" for="password">Password</label>
      <input type="password" name="password" class="form-control" placeholder="Masukkan password" required />

      <button type="submit" class="btn-primary">Login</button>
    </form>

    <p class="text-center">Belum punya akun? <a href="register.php">Daftar</a></p>
  </div>

  <div class="wave-bottom">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320">
      <path fill="#fb8a06" fill-opacity="1"
        d="M0,288L30,250.7C60,213,120,139,180,133.3C240,128,300,192,360,224C420,256,480,256,540,250.7C600,245,660,235,720,208C780,181,840,139,900,138.7C960,139,1020,181,1080,213.3C1140,245,1200,267,1260,261.3C1320,256,1380,224,1410,208L1440,192L1440,320L1410,320C1380,320,1320,320,1260,320C1200,320,1140,320,1080,320C1020,320,960,320,900,320C840,320,780,320,720,320C660,320,600,320,540,320C480,320,420,320,360,320C300,320,240,320,180,320C120,320,60,320,30,320L0,320Z">
      </path>
    </svg>
  </div>
</body>
</html>