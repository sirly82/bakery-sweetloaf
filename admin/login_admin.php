<?php
session_start();
require '../db_connect.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (empty($username) || empty($password)) {
        $error = "Username dan password harus diisi.";
    } else {
        $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password']) && $user['role'] === 'admin') {
                $_SESSION['admin_username'] = $user['username'];
                $_SESSION['admin_id'] = $user['id'];
                $_SESSION['admin_role'] = $user['role'];

                header("Location: dashboard.php");
                exit();
            } else {
                $error = "Username atau password salah, atau Anda tidak memiliki akses admin.";
            }
        } else {
            $error = "Username atau password salah, atau Anda tidak memiliki akses admin.";
        }
        $stmt->close();
    }
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - SweetLoaf Bakery</title>
    <link rel="stylesheet" href="assets/styles/admin_style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    </head>
<body class="login-page"> <div class="overlay"></div>
    <div class="login-admin-container"> <div class="logo-admin">
            <img src="../assets/logo.png" alt="SweetLoaf Bakery Admin Logo">
        </div>
        <h2>Login Admin</h2>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <form action="login_admin.php" method="post">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" placeholder="Masukkan username Anda" required autocomplete="username">
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" placeholder="Masukkan password Anda" required autocomplete="current-password">
            </div>
            <button type="submit" class="btn-primary">Login</button>
        </form>
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