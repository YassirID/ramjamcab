<?php
session_start();
include('../includes/db.php');

// Cek autentikasi
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

$role = $_SESSION['role'];
$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

switch ($role) {
    case 'admin':
        $sql_total_peserta = "SELECT COUNT(*) as total FROM peserta";
        break;
    case 'cabang':
    case 'selatan':
    case 'utara':
    case 'tengah':
    case 'saka':
        $sql_total_peserta = "SELECT COUNT(*) as total 
                              FROM peserta 
                              JOIN users ON peserta.user_id = users.id 
                              WHERE users.role = '$role'";
        break;
    default:
        $sql_total_peserta = "SELECT COUNT(*) as total 
                              FROM peserta 
                              WHERE user_id = '$user_id'";
        break;
}


$result_total_peserta = $conn->query($sql_total_peserta);
if ($result_total_peserta && $result_total_peserta->num_rows > 0) {
    $row_total = $result_total_peserta->fetch_assoc();
    $total_peserta = $row_total['total'] ?? 0;
}

// Ambil total jumlah peserta terdaftar dan nominal berdasarkan user_id
$sql_terdaftar = "SELECT SUM(jumlah_terdaftar) AS total_terdaftar FROM bukti_pembayaran WHERE user_id = '$user_id'";
$result_terdaftar = $conn->query($sql_terdaftar);
$total_terdaftar = ($result_terdaftar && $result_terdaftar->num_rows > 0) ? $result_terdaftar->fetch_assoc()['total_terdaftar'] : 0;

$sql_nominal = "SELECT SUM(nominal) as total_nominal FROM bukti_pembayaran WHERE user_id = '$user_id'";
$result_nominal = $conn->query($sql_nominal);
$total_nominal = ($result_nominal && $result_nominal->num_rows > 0) ? $result_nominal->fetch_assoc()['total_nominal'] : 0;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard <?php echo ucfirst($role); ?> - Raimuna Jawa Barat XIV</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="font-sans bg-gray-100 flex h-screen">
    <!-- Sidebar -->
 <?php include 'sidebar.php'; ?>


    <!-- Main Content -->
    <div class="main-content flex-1 p-10 md:ml-64">
        <header class="bg-white shadow p-6 rounded-lg mb-6">
            <h1 class="text-3xl font-bold text-gray-800">Selamat Datang, <?php echo ucfirst($role); ?> <?php echo htmlspecialchars($username); ?>!</h1>
        </header>
        <section class="content bg-white shadow p-6 rounded-lg">
            <p class="text-gray-700 mb-6">Ini adalah halaman dashboard untuk role <strong><?php echo $role; ?></strong>. Anda dapat mengelola dan melihat informasi sesuai hak akses.</p>
            <div class="summary-boxes grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="box bg-blue-500 text-white p-6 rounded-lg shadow-md">
                    <h3 class="text-xl font-semibold mb-2">Total Peserta</h3>
                    <p class="text-3xl font-bold"><?php echo $total_peserta; ?> orang</p>
                </div>
                <div class="box bg-green-500 text-white p-6 rounded-lg shadow-md">
                    <h3 class="text-xl font-semibold mb-2">Total Terdaftar</h3>
                    <p class="text-3xl font-bold"><?php echo $total_terdaftar; ?> orang</p>
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
