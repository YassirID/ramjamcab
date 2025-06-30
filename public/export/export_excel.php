<?php
include('./includes/db.php');
require '../vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

session_start();
$role = $_SESSION['role'];
$user_id = $_SESSION['user_id'];
$kontingen = $_GET['kontingen'] ?? '';

include('../functions/berkas_functions.php');
$data = getFilteredBerkas($conn, $role, $user_id, $kontingen);

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->fromArray(['No', 'Nama Berkas', 'File', 'Status', 'Catatan', 'Upload By', 'Upload Date'], NULL, 'A1');

$no = 2;
$index = 1;
while ($row = $data->fetch_assoc()) {
    $sheet->fromArray([
        $index++,
        $row['nama_berkas'],
        $row['file_path'],
        $row['status'],
        $row['catatan_dokumen'],
        $row['username'],
        $row['uploaded_at']
    ], NULL, "A$no");
    $no++;
}

$writer = new Xlsx($spreadsheet);
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="berkas_kontingen.xlsx"');
$writer->save('php://output');
exit;
