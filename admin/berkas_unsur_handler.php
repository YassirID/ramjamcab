<?php
session_start();
include('../includes/db.php');

if (!isset($_SESSION['user_id']) || !isset($_GET['action']) || $_GET['action'] !== 'delete') {
    header("Location: data_unsur_kontingen.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$id = $conn->real_escape_string($_GET['id']);
$type = $conn->real_escape_string($_GET['type']);
$allowedTypes = ['pas_foto', 'kta', 'asuransi', 'sertifikat_sfh'];

if (!in_array($type, $allowedTypes)) {
    $_SESSION['prg_error'] = "Jenis berkas tidak valid.";
    header("Location: data_unsur_kontingen.php");
    exit();
}

// Ambil file
$sql = "SELECT {$type}_path FROM unsur_kontingen WHERE id = '$id' AND user_id = '$user_id'";
$result = $conn->query($sql);
if ($result->num_rows == 1) {
    $data = $result->fetch_assoc();
    $file = $data[$type . '_path'];
    if ($file && file_exists("../uploads/berkas_unsur/$file")) {
        unlink("../uploads/berkas_unsur/$file");
    }

    // Kosongkan field di DB
    $conn->query("UPDATE unsur_kontingen SET {$type}_path = NULL WHERE id = '$id' AND user_id = '$user_id'");
    $_SESSION['prg_message'] = "Berkas $type berhasil dihapus.";
} else {
    $_SESSION['prg_error'] = "Data tidak ditemukan.";
}

header("Location: data_unsur_kontingen.php");
exit();
