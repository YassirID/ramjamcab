<?php
$servername = "localhost";
$username = "root"; // Ganti dengan username database Anda
$password = "";     // Ganti dengan password database Anda
$dbname = "ramcab"; // Ganti dengan nama database Anda

// Membuat koneksi
$conn = new mysqli($servername, $username, $password, $dbname);

// Memeriksa koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}
// echo "Koneksi berhasil!"; // Hanya untuk testing
?>