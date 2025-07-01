<?php
// db_connect.php

$servername = "localhost"; // Ganti dengan host database Anda
$username = "root";      // Ganti dengan username database Anda
$password = "";          // Ganti dengan password database Anda
$dbname = "bakery";      // Nama database seperti yang didefinisikan di bakery.sql

// Buat koneksi
$conn = new mysqli($servername, $username, $password, $dbname);

// Cek koneksi
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
// echo "Connected successfully"; // Uncomment untuk pengujian koneksi
?>