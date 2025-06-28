<?php
session_start();
include('../includes/db.php');

// Periksa apakah user sudah login dan memiliki role pinkonran
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'pinkonran') {
    header("Location: ../index.php"); // Redirect ke halaman login jika belum login/role tidak sesuai
    exit();
}

$user_id = $_SESSION['user_id'];

// Anda bisa mengambil data ringkasan khusus untuk Pinkonran di sini
// Misalnya, jumlah peserta yang sudah diinput oleh Pinkonran ini
$total_peserta_diinput = 0;
$sql_count_peserta = "SELECT COUNT(id) AS total_peserta FROM peserta WHERE user_id = '$user_id'";
$result_count_peserta = $conn->query($sql_count_peserta);
if ($result_count_peserta->num_rows > 0) {
    $row_count_peserta = $result_count_peserta->fetch_assoc();
    $total_peserta_diinput = $row_count_peserta['total_peserta'];
}

$total_unsur_diinput = 0;
$sql_count_unsur = "SELECT COUNT(id) AS total_unsur FROM unsur_kontingen WHERE user_id = '$user_id'";
$result_count_unsur = $conn->query($sql_count_unsur);
if ($result_count_unsur->num_rows > 0) {
    $row_count_unsur = $result_count_unsur->fetch_assoc();
    $total_unsur_diinput = $row_count_unsur['total_unsur'];
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Pinkonran - Raimuna Jawa Barat XIV</title>
    <script src="https://cdn.tailwindcss.com"></script>
    </head>
<body class="font-sans bg-gray-100 flex h-screen">
      <div class="sidebar bg-gray-800 text-white w-64 space-y-6 py-7 px-2 fixed inset-y-0 left-0">
     <a href="" >
        <img src="../src/img/GRAPHIC.png" alt="Logo Raimuna Cabang Cimahi" class="w-48 mx-auto mb-6">
    </a>
    <nav>
        <ul class="space-y-2">
            <li><a href="dashboard_<?php echo $role; ?>.php" class="block py-2.5 px-4 rounded bg-gray-700">Dashboard</a></li>
            <li><a href="data_berkas_kontingen.php" class="block py-2.5 px-4 rounded hover:bg-gray-700">Data Berkas Kontingen</a></li>
            <li><a href="data_unsur_kontingen.php" class="block py-2.5 px-4 rounded hover:bg-gray-700">Data Unsur Kontingen</a></li>
                <li><a href="data_peserta.php" class="block py-2.5 px-4 rounded transition duration-200 hover:bg-gray-700 ">Data Peserta</a></li
            <li><a href="data_peserta_jamcab.php" class="block py-2.5 px-4 rounded hover:bg-gray-700">Data Peserta JamCab</a></li>
            <li><a href="../logout.php" class="block py-2.5 px-4 rounded hover:bg-gray-700">Logout</a></li>
        </ul>
    </nav>
     </div>
    <div class="main-content flex-1 p-10 md:ml-64">
        <header class="bg-white shadow p-6 rounded-lg mb-6">
            <h1 class="text-3xl font-bold text-gray-800">Selamat Datang, Pinkonran <?php echo $_SESSION['username']; ?>!</h1>
        </header>
        <section class="content bg-white shadow p-6 rounded-lg">
            <p class="text-gray-700 mb-6">Ini adalah halaman dashboard untuk Pinkonran. Anda dapat mengelola berkas kontingen, data unsur kontingen, dan data peserta.</p>

            <div class="summary-boxes flex justify-around mb-6 space-x-4">
                <div class="box bg-gray-50 p-6 rounded-lg shadow-md text-center flex-1">
                    <h3 class="text-lg font-semibold text-gray-700 mb-2">Total Peserta Diinput</h3>
                    <p class="text-2xl font-bold text-blue-600"><?php echo $total_peserta_diinput; ?> Orang</p>
                </div>
                <div class="box bg-gray-50 p-6 rounded-lg shadow-md text-center flex-1">
                    <h3 class="text-lg font-semibold text-gray-700 mb-2">Total Unsur Kontingen Diinput</h3>
                    <p class="text-2xl font-bold text-blue-600"><?php echo $total_unsur_diinput; ?> Orang</p>
                </div>
            </div>
        </section>
    </div>
</body>
</html>