<?php
include('../includes/db.php');
session_start();

$role = $_SESSION['role'];
$user_id = $_SESSION['user_id'];
$kontingen = $_GET['kontingen'] ?? '';

// Query disesuaikan dengan role
include('../functions/berkas_functions.php');
$data = getFilteredBerkas($conn, $role, $user_id, $kontingen);

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="berkas_kontingen.csv"');

$output = fopen("php://output", "w");
fputcsv($output, ['No', 'Nama Berkas', 'File', 'Status', 'Catatan', 'Upload By', 'Upload Date']);

$no = 1;
while ($row = $data->fetch_assoc()) {
    fputcsv($output, [
        $no++,
        $row['nama_berkas'],
        $row['file_path'],
        $row['status'],
        $row['catatan_dokumen'],
        $row['username'],
        $row['uploaded_at']
    ]);
}
fclose($output);
exit;
