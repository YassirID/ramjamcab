<?php
include('../includes/db.php');
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $conn->real_escape_string($_POST['berkas_id']);
    $nama = $conn->real_escape_string($_POST['nama_berkas']);
    $catatan = $conn->real_escape_string($_POST['catatan_dokumen']);
    $status = $conn->real_escape_string($_POST['status']);

    $sql = "UPDATE berkas_kontingen SET 
            nama_berkas = '$nama', 
            catatan_dokumen = '$catatan', 
            status = '$status' 
            WHERE id = '$id'";

    if ($conn->query($sql)) {
        $_SESSION['prg_message'] = "Data berhasil diupdate.";
    } else {
        $_SESSION['prg_error'] = "Gagal mengupdate: " . $conn->error;
    }

    header("Location: ../public/data_berkas_kontingen.php");
    exit;
}
