<?php $activePage = basename($_SERVER['PHP_SELF']); ?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="../src/output.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

</head>
<body>
<!-- Sidebar (hanya tampil di md ke atas) -->
<?php
$current_page = basename($_SERVER['PHP_SELF']); // contoh: dashboard.php
?>

<!-- Sidebar (Desktop) -->
<div class="hidden md:block bg-gray-800 text-white w-64 py-7 px-2 fixed inset-y-0 left-0">
  <a href="#"><img src="../src/img/GRAPHIC.png" alt="Logo" class="w-48 mx-auto mb-6"></a>
  <nav>
    <ul class="space-y-2">
      <li><a href="dashboard.php" class="flex items-center gap-2 px-4 py-2.5 rounded <?= $current_page == 'dashboard.php' ? 'bg-gray-700' : 'hover:bg-gray-700' ?>"><span class="material-icons">home</span>Dashboard</a></li>
      <li><a href="data_berkas_kontingen.php" class="flex items-center gap-2 px-4 py-2.5 rounded <?= $current_page == 'data_berkas_kontingen.php' ? 'bg-gray-700' : 'hover:bg-gray-700' ?>"><span class="material-icons">folder</span>Berkas Kontingen</a></li>
      <li><a href="data_unsur_kontingen.php" class="flex items-center gap-2 px-4 py-2.5 rounded <?= $current_page == 'data_unsur_kontingen.php' ? 'bg-gray-700' : 'hover:bg-gray-700' ?>"><span class="material-icons">group</span>Unsur Kontingen</a></li>
      <li><a href="bukti_pembayaran.php" class="flex items-center gap-2 px-4 py-2.5 rounded <?= $current_page == 'bukti_pembayaran.php' ? 'bg-gray-700' : 'hover:bg-gray-700' ?>"><span class="material-icons">receipt_long</span>Pembayaran</a></li>
      <li><a href="data_peserta.php" class="flex items-center gap-2 px-4 py-2.5 rounded <?= $current_page == 'data_peserta.php' ? 'bg-gray-700' : 'hover:bg-gray-700' ?>"><span class="material-icons">badge</span>Peserta</a></li>
      <li><a href="data_peserta_jamcab.php" class="flex items-center gap-2 px-4 py-2.5 rounded <?= $current_page == 'data_peserta_jamcab.php' ? 'bg-gray-700' : 'hover:bg-gray-700' ?>"><span class="material-icons">event_note</span>Jamcab</a></li>
      <li><a href="../logout.php" class="flex items-center gap-2 px-4 py-2.5 rounded hover:bg-gray-700"><span class="material-icons">logout</span>Logout</a></li>
    </ul>
  </nav>
</div>

<!-- Bottom Nav (Mobile) -->
<div class="fixed bottom-0 inset-x-0 bg-gray-800 text-white flex justify-around items-center py-2 md:hidden z-50 shadow">
  <a href="dashboard.php" class="flex flex-col items-center text-xs <?= $current_page == 'dashboard.php' ? 'text-blue-400' : '' ?>"><span class="material-icons">home</span>Dashboard</a>
  <a href="data_berkas_kontingen.php" class="flex flex-col items-center text-xs <?= $current_page == 'data_berkas_kontingen.php' ? 'text-blue-400' : '' ?>"><span class="material-icons">folder</span>Berkas</a>
  <a href="data_unsur_kontingen.php" class="flex flex-col items-center text-xs <?= $current_page == 'data_unsur_kontingen.php' ? 'text-blue-400' : '' ?>"><span class="material-icons">group</span>Unsur</a>
  <a href="bukti_pembayaran.php" class="flex flex-col items-center text-xs <?= $current_page == 'bukti_pembayaran.php' ? 'text-blue-400' : '' ?>"><span class="material-icons">receipt_long</span>Pembayaran</a>
  <a href="data_peserta.php" class="flex flex-col items-center text-xs <?= $current_page == 'data_peserta.php' ? 'text-blue-400' : '' ?>"><span class="material-icons">badge</span>Peserta</a>
  <a href="data_peserta_jamcab.php" class="flex flex-col items-center text-xs <?= $current_page == 'data_peserta_jamcab.php' ? 'text-blue-400' : '' ?>"><span class="material-icons">event_note</span>Jamcab</a>
  <a href="../logout.php" class="flex flex-col items-center text-xs"><span class="material-icons">logout</span>Logout</a>
</div>

</body>
</html>