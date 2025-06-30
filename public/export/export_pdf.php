<?php
require '../vendor/autoload.php';
include('./includes/db.php');
session_start();

use Dompdf\Dompdf;
use Dompdf\Options;

$role = $_SESSION['role'];
$user_id = $_SESSION['user_id'];
$kontingen = $_GET['kontingen'] ?? '';

include('../functions/berkas_functions.php');
$data = getFilteredBerkas($conn, $role, $user_id, $kontingen);

$html = "<h2>Data Berkas Kontingen</h2><table border='1' cellpadding='5' cellspacing='0'><thead><tr>
<th>No</th><th>Nama Berkas</th><th>File</th><th>Status</th><th>Catatan</th><th>Upload By</th><th>Tanggal</th></tr></thead><tbody>";

$no = 1;
while ($row = $data->fetch_assoc()) {
    $html .= "<tr>
    <td>{$no}</td><td>{$row['nama_berkas']}</td><td>{$row['file_path']}</td>
    <td>{$row['status']}</td><td>{$row['catatan_dokumen']}</td>
    <td>{$row['username']}</td><td>{$row['uploaded_at']}</td></tr>";
    $no++;
}
$html .= "</tbody></table>";

$options = new Options();
$options->set('defaultFont', 'Helvetica');
$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();
$dompdf->stream("berkas_kontingen.pdf", array("Attachment" => true));
