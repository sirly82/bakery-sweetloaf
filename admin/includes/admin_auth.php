<?php
// Pastikan session sudah dimulai di halaman yang memanggil file ini
// Jika belum, panggil session_start() di awal setiap file admin utama (misal: dashboard.php)
// atau pastikan ini dipanggil di login_admin.php sebelum redirect

// db_connect.php sudah di-require di file-file utama seperti login_admin.php,
// jadi kita tidak perlu me-require-nya lagi di sini kecuali ada kebutuhan database spesifik
// untuk otentikasi yang belum tercakup.

// Cek apakah sesi sudah dimulai
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Cek apakah variabel sesi 'admin_username' (atau 'admin_id', 'admin_role') sudah diset
// dan apakah peran (role) pengguna adalah 'admin'.
// Kita akan menggunakan 'admin_username' dan 'admin_role' yang diset di login_admin.php
if (!isset($_SESSION['admin_username']) || !isset($_SESSION['admin_role']) || $_SESSION['admin_role'] !== 'admin') {
    // Jika tidak login sebagai admin, arahkan ke halaman login admin
    header("Location: login_admin.php");
    exit(); // Penting untuk menghentikan eksekusi skrip lebih lanjut
}

// Jika sudah login dan memiliki akses admin, skrip akan melanjutkan ke halaman yang memanggil file ini.
// Anda bisa menambahkan logikasi lain di sini jika diperlukan,
// seperti:
// - Memperbarui waktu aktivitas sesi
// - Memeriksa IP address
// - Log akses
?>
