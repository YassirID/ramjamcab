<?php
session_start();
include('../includes/db.php');

// Cek autentikasi
if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'pinkoncab') {
    header("Location: ../index.php");
    exit();
}
$username = $_SESSION['username'];
// Ambil total jumlah peserta dari tabel peserta berdasarkan user_id
$sql_total_peserta = "SELECT COUNT(*) as total FROM peserta";
$result_total_peserta = $conn->query($sql_total_peserta);

// Cek hasil dan simpan jumlah peserta
$total_peserta = 0;
if ($result_total_peserta && $result_total_peserta->num_rows > 0) {
    $row_total = $result_total_peserta->fetch_assoc();
    $total_peserta = $row_total['total'] ?? 0;
}


// Ambil total jumlah peserta yang terdaftar dari semua pembayaran user
$sql_terdaftar = "SELECT SUM(jumlah_terdaftar) AS total_terdaftar FROM bukti_pembayaran WHERE user_id = '$user_id'";
$result_terdaftar = $conn->query($sql_terdaftar);
$total_terdaftar = 0;

if ($result_terdaftar && $result_terdaftar->num_rows > 0) {
    $row = $result_terdaftar->fetch_assoc();
    $total_terdaftar = $row['total_terdaftar'] ?? 0;
}


// Ambil total nominal dari tabel pembayaran
$sql_nominal = "SELECT SUM(nominal) as total_nominal FROM bukti_pembayaran WHERE id = '$username'";
$result_nominal = $conn->query($sql_nominal);
$total_nominal = ($result_nominal && $result_nominal->num_rows > 0) ? $result_nominal->fetch_assoc()['total_nominal'] : 0;

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Pinkoncab - Raimuna Jawa Barat XIV</title>
    <script src="https://cdn.tailwindcss.com"></script>
    </head>
<body class="font-sans bg-gray-100 flex h-screen">
     <div class="sidebar bg-gray-800 text-white w-64 space-y-6 py-7 px-2 fixed inset-y-0 left-0">
     <a href="">
        <img src="../src/img/GRAPHIC.png" alt="Logo Raimuna Cabang Cimahi" class="w-48 mx-auto mb-6">
    </a>

    <nav>
        <ul class="space-y-2">
            <li><a href="dashboard_<?php echo $role; ?>.php" class="block py-2.5 px-4 rounded bg-gray-700">Dashboard</a></li>
            <li><a href="data_berkas_kontingen.php" class="block py-2.5 px-4 rounded hover:bg-gray-700">Data Berkas Kontingen</a></li>
            <li><a href="data_unsur_kontingen.php" class="block py-2.5 px-4 rounded hover:bg-gray-700">Data Unsur Kontingen</a></li>
                <l><a href="bukti_pembayaran.php" class="block py-2.5 px-4 rounded hover:bg-gray-700">Bukti Pembayaran</a></l
                <li><a href="data_peserta.php" class="block py-2.5 px-4 rounded transition duration-200 hover:bg-gray-700 ">Data Peserta</a></li
            <li><a href="data_peserta_jamcab.php" class="block py-2.5 px-4 rounded hover:bg-gray-700">Data Peserta JamCab</a></li>
            <li><a href="../logout.php" class="block py-2.5 px-4 rounded hover:bg-gray-700">Logout</a></li>
        </ul>
    </nav>
</div>
    <div class="main-content flex-1 p-10 md:ml-64">
        <header class="bg-white shadow p-6 rounded-lg mb-6">
            <h1 class="text-3xl font-bold text-gray-800">Selamat Datang, Pinkoncab <?php echo htmlspecialchars($username); ?>!</h1>
        </header>
        <section class="content bg-white shadow p-6 rounded-lg">
            <p class="text-gray-700 mb-6">Ini adalah halaman dashboard untuk Pinkoncab. Anda dapat mengelola berkas kontingen, data unsur kontingen, bukti pembayaran, dan melihat data peserta.</p>
            <div class="summary-boxes grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="box bg-blue-500 text-white p-6 rounded-lg shadow-md">
                    <h3 class="text-xl font-semibold mb-2">Total Peserta</h3>
                    <p class="text-3xl font-bold"><?php echo $total_peserta; ?> orang</p>
                </div>
                <div class="box bg-green-500 text-white p-6 rounded-lg shadow-md">
                    <h3 class="text-xl font-semibold mb-2">Total Terdaftar</h3>
                    <p class="text-3xl font-bold"><?php echo $total_terdaftar; ?> Orang</p>
                </div>
                <div class="box bg-yellow-500 text-white p-6 rounded-lg shadow-md">
                    <h3 class="text-xl font-semibold mb-2">Total Nominal</h3>
                    <p class="text-3xl font-bold">Rp. <?php echo number_format($total_nominal, 0, ',', '.'); ?></p>
                </div>
            </div>
        </section>
    </div>

     
</body>
</html>
