<?php
session_start();
include('../../includes/db.php');


// Cek hak akses
$allowed_roles = ['admin', 'cabang', 'selatan', 'utara', 'tengah', 'saka'];
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], $allowed_roles)) {
    http_response_code(403);
    die("Access denied.");
}

$role = $_SESSION['role'];
$filter = $_POST['post_by_filter'] ?? '';

$sql = "SELECT * FROM berkas_kontingen";
$conditions = [];

// Filter berdasarkan role pengguna
if (!in_array($role, ['admin', 'cabang'])) {
    $conditions[] = "post_by = '" . $conn->real_escape_string($role) . "'";
} elseif (!empty($filter)) {
    $conditions[] = "post_by = '" . $conn->real_escape_string($filter) . "'";
}

if (!empty($conditions)) {
    $sql .= " WHERE " . implode(" AND ", $conditions);
}

$sql .= " ORDER BY uploaded_at DESC";

// Eksekusi query
$result = $conn->query($sql);
if (!$result) {
    die("Query error: " . $conn->error);
}

// Output CSV
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="data_berkas_kontingen.csv"');

$output = fopen('php://output', 'w');

// Header kolom CSV
fputcsv($output, ['Nama Berkas', 'File', 'Status', 'Catatan', 'Tanggal Upload', 'Post By']);

// Isi data
while ($row = $result->fetch_assoc()) {
    fputcsv($output, [
        $row['nama_berkas'],
        $row['file_path'],
        $row['status'],
        $row['catatan_dokumen'],
        $row['uploaded_at'],
        $row['post_by']
    ]);
}

fclose($output);
exit;
