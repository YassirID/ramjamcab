<?php
session_start();
include('../includes/db.php');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'pinkoncab') {
    header("Location: ../index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$id = $_GET['id'] ?? null;

if (!$id) {
    header("Location: bukti_pembayaran.php");
    exit();
}

// Ambil data untuk ditampilkan
$sql = "SELECT * FROM bukti_pembayaran WHERE id = '$id' AND user_id = '$user_id'";
$result = $conn->query($sql);

if ($result->num_rows !== 1) {
    $_SESSION['error'] = "Data tidak ditemukan.";
    header("Location: bukti_pembayaran.php");
    exit();
}

$data = $result->fetch_assoc();

// Proses update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $jumlah = $_POST['jumlah_terdaftar'];
    $nominal = $_POST['nominal'];
    $update_sql = "UPDATE bukti_pembayaran SET jumlah_terdaftar='$jumlah', nominal='$nominal' WHERE id='$id' AND user_id='$user_id'";
    if ($conn->query($update_sql)) {
        $_SESSION['message'] = "Data berhasil diupdate.";
        header("Location: bukti_pembayaran.php");
        exit();
    } else {
        $_SESSION['error'] = "Gagal update: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html>
<head><title>Edit Bukti Pembayaran</title></head>
<body>
<h2>Edit Pembayaran</h2>
<form method="POST">
    Jumlah Terdaftar: <input type="number" name="jumlah_terdaftar" value="<?= $data['jumlah_terdaftar'] ?>" required><br>
    Nominal: <input type="number" name="nominal" value="<?= $data['nominal'] ?>" required><br>
    <button type="submit">Update</button>
    <a href="bukti_pembayaran.php">Batal</a>
</form>
</body>
</html>
