<?php
if (!isset($_SESSION['role'])) die("Unauthorized access");
$role = $_SESSION['role'];
$username = $_SESSION['username'];
?>


<aside class="bg-gray-800 w-64 h-full fixed text-white">
  <div class="sidebar bg-gray-800 text-white w-64 space-y-6 py-7 px-2 fixed inset-y-0 left-0 z-40">
  <a href="/public/dashboard.php">
    <img src="../src/img/GRAPHIC.png" alt="Logo Raimuna" class="w-40 mx-auto mb-4">
  </a>
  <h2 class="text-center text-white text-xl font-semibold mb-6">DKD JAWA BARAT</h2>

  <nav>
    <ul class="space-y-2">
      <li><a href="./dashboard.php" class="block py-2.5 px-4 rounded hover:bg-gray-700 bg-gray-700">Dashboard</a></li>
      <li><a href="./data_berkas_kontingen.php" class="block py-2.5 px-4 rounded hover:bg-gray-700">Data Berkas Kontingen</a></li>
      <li><a href="./data_unsur_kontingen.php" class="block py-2.5 px-4 rounded hover:bg-gray-700">Data Unsur Kontingen</a></li>

      <?php if ($role === 'cabang'): ?>
        <li><a href="../admin/bukti_pembayaran.php" class="block py-2.5 px-4 rounded hover:bg-gray-700">Bukti Pembayaran</a></li>
      <?php endif; ?>

      <li><a href="../admin/data_peserta.php" class="block py-2.5 px-4 rounded hover:bg-gray-700">Data Peserta</a></li>
      <li><a href="../admin/data_peserta_jamcab.php" class="block py-2.5 px-4 rounded hover:bg-gray-700">Data Peserta Jamcab</a></li>
      <li><a href="../logout.php" class="block py-2.5 px-4 rounded hover:bg-gray-700">Logout</a></li>
    </ul>
  </nav>
</div>
</aside>


