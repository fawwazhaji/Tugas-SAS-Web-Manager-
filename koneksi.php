<?php
$host = "localhost";
$user = "root";
$pass = ""; // Kosongkan jika pakai Laragon/XAMPP bawaan
$db   = "db_flag_manager"; // Nama database yang tadi dibuat

$koneksi = mysqli_connect($host, $user, $pass, $db);

if (!$koneksi) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
?>