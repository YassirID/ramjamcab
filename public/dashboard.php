<?php
session_start();
include('../includes/db.php');
include('../includes/functions.php');
include('../includes/auth.php');

$username = $_SESSION['username'];
$role = $_SESSION['role'];
$user_id = $_SESSION['user_id'];

$total_peserta = getTotalPeserta($conn, $role, $user_id);
$total_verifikasi = getTotalPesertaTerverifikasi($conn, $role, $user_id);
$total_bayar = getTotalPembayaran($conn, $role, $user_id);
$progress = $total_peserta > 0 ? round(($total_verifikasi / $total_peserta) * 100) : 0;

include('../templates/header.php');
include('../templates/sidebar.php');
?>

<div class="main-content flex-1 p-6 md:ml-64 bg-gray-50 min-h-screen w-full px-4">
  <header class="bg-white shadow-md p-6 rounded-lg mb-6 flex justify-between items-center">
    <div>
      <h1 class="text-3xl font-extrabold text-gray-800">Hai, <?php echo htmlspecialchars($username); ?> 👋</h1>
      <p class="text-gray-500 text-sm mt-1">Role Anda: <span class="font-medium capitalize"><?php echo $role; ?></span></p>
    </div>
    <span class="bg-blue-100 text-blue-700 text-xs font-medium px-3 py-1 rounded-full hidden md:inline">Dashboard Raimuna XIV</span>
  </header>

  <section class="bg-white shadow p-6 rounded-lg">
    <h2 class="text-lg font-semibold text-gray-700 mb-4">Ringkasan Data Kontingen</h2>
    
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
      <div class="bg-gradient-to-br from-blue-500 to-blue-600 text-white p-5 rounded-lg shadow-md hover:shadow-lg transition">
        <h3 class="text-sm font-semibold mb-1">Total Peserta</h3>
        <p class="text-3xl font-bold"><?php echo $total_peserta; ?> Orang</p>
      </div>

      <div class="bg-gradient-to-br from-green-500 to-green-600 text-white p-5 rounded-lg shadow-md hover:shadow-lg transition">
        <h3 class="text-sm font-semibold mb-1">Terverifikasi</h3>
        <p class="text-3xl font-bold"><?php echo $total_verifikasi; ?> Orang</p>
      </div>

      <div class="bg-gradient-to-br from-yellow-500 to-yellow-600 text-white p-5 rounded-lg shadow-md hover:shadow-lg transition">
        <h3 class="text-sm font-semibold mb-1">Total Pembayaran</h3>
        <p class="text-xl font-bold">Rp <?php echo number_format($total_bayar, 0, ',', '.'); ?></p>
      </div>

      <div class="bg-gradient-to-br from-indigo-500 to-indigo-600 text-white p-5 rounded-lg shadow-md hover:shadow-lg transition">
        <h3 class="text-sm font-semibold mb-1">Progress Data</h3>
        <div class="mt-2">
          <div class="w-full bg-indigo-200 rounded-full h-3 mb-2">
            <div class="bg-white h-3 rounded-full" style="width: <?php echo $progress; ?>%"></div>
          </div>
          <p class="text-sm font-medium"><?php echo $progress; ?>%</p>
        </div>
      </div>
    </div>

    <p class="text-sm text-gray-600">Gunakan menu di samping untuk mengelola data peserta, berkas kontingen, dan pembayaran. Pastikan semua data Anda terisi dengan benar.</p>
  </section>
  <?php include('../templates/footer.php'); ?>
</div>


