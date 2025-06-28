<?php
session_start();
include('../includes/db.php');

// Periksa apakah user sudah login dan memiliki role pinkoncab
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'pinkoncab') {
    header("Location: ../index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// --- Handle Add Bukti Pembayaran ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_pembayaran'])) {
    $jumlah_terdaftar = $conn->real_escape_string($_POST['jumlah_terdaftar']);
    $nominal = $conn->real_escape_string($_POST['nominal']);

    $target_dir = "../uploads/bukti_pembayaran/";
    $file_name = uniqid("payment_") . "_" . basename($_FILES["bukti_file"]["name"]);
    $target_file = $target_dir . $file_name;
    $uploadOk = 1;
    $fileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    // Periksa tipe file (hanya gambar/pdf)
    if($fileType != "jpg" && $fileType != "png" && $fileType != "jpeg" && $fileType != "gif" && $fileType != "pdf") {
        $_SESSION['error'] = "Maaf, hanya file JPG, JPEG, PNG, GIF & PDF yang diizinkan.";
        $uploadOk = 0;
    }

    if ($uploadOk == 0) {
        $_SESSION['error'] = $_SESSION['error'] ?? "Maaf, file Anda tidak terunggah.";
    } else {
        if (move_uploaded_file($_FILES["bukti_file"]["tmp_name"], $target_file)) {
            $sql = "INSERT INTO bukti_pembayaran (user_id, jumlah_terdaftar, nominal, bukti_pembayaran_path, status)
                    VALUES ('$user_id', '$jumlah_terdaftar', '$nominal', '$file_name', 'pending')";
            if ($conn->query($sql) === TRUE) {
                $_SESSION['message'] = "Bukti pembayaran berhasil diunggah.";
            } else {
                $_SESSION['error'] = "Error: " . $sql . "<br>" . $conn->error;
            }
        } else {
            $_SESSION['error'] = "Maaf, terjadi kesalahan saat mengunggah file Anda.";
        }
    }
    header("Location: bukti_pembayaran.php");
    exit();
}

// --- Handle Delete Bukti Pembayaran ---
if (isset($_GET['delete_pembayaran_id'])) {
    $id_to_delete = $conn->real_escape_string($_GET['delete_pembayaran_id']);

    $sql_get_file = "SELECT bukti_pembayaran_path FROM bukti_pembayaran WHERE id = '$id_to_delete' AND user_id = '$user_id'";
    $result_get_file = $conn->query($sql_get_file);
    if ($result_get_file->num_rows > 0) {
        $row_file = $result_get_file->fetch_assoc();
        $file_to_delete = "../uploads/bukti_pembayaran/" . $row_file['bukti_pembayaran_path'];
        if (file_exists($file_to_delete)) {
            unlink($file_to_delete); // Hapus file fisik
        }
        $sql_delete = "DELETE FROM bukti_pembayaran WHERE id = '$id_to_delete' AND user_id = '$user_id'";
        if ($conn->query($sql_delete) === TRUE) {
            $_SESSION['message'] = "Bukti pembayaran berhasil dihapus.";
        } else {
            $_SESSION['error'] = "Error deleting record: " . $conn->error;
        }
    }
    header("Location: bukti_pembayaran.php");
    exit();
}

// Ambil data pembayaran
$sql_pembayaran = "SELECT * FROM bukti_pembayaran WHERE user_id = '$user_id' ORDER BY tanggal_upload DESC";
$result_pembayaran = $conn->query($sql_pembayaran);

// Hitung total peserta terdaftar dan total nominal
$total_peserta_terdaftar = 0;
$total_nominal_pembayaran = 0;
$sql_summary = "SELECT SUM(jumlah_terdaftar) AS total_terdaftar, SUM(nominal) AS total_nominal FROM bukti_pembayaran WHERE user_id = '$user_id'";
$result_summary = $conn->query($sql_summary);
if ($result_summary->num_rows > 0) {
    $summary_row = $result_summary->fetch_assoc();
    $total_peserta_terdaftar = $summary_row['total_terdaftar'] ?? 0;
    $total_nominal_pembayaran = $summary_row['total_nominal'] ?? 0;
}

// Tampilkan pesan sukses/error
$message = '';
$error = '';
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
}
if (isset($_SESSION['error'])) {
    $error = $_SESSION['error'];
    unset($_SESSION['error']);
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bukti Pembayaran - Raimuna Jawa Barat XIV</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Gaya dasar untuk modal yang mungkin tidak sepenuhnya bisa digantikan oleh utilitas Tailwind saja */
        .modal {
            display: none; /* Hidden by default */
            position: fixed; /* Stay in place */
            z-index: 50; /* Sit on top */
            left: 0;
            top: 0;
            width: 100%; /* Full width */
            height: 100%; /* Full height */
            overflow: auto; /* Enable scroll if needed */
            background-color: rgba(0,0,0,0.4); /* Black w/ opacity */
        }
    </style>
</head>
<body class="font-sans bg-gray-100 flex h-screen">
 <div class="sidebar bg-gray-800 text-white w-64 space-y-6 py-7 px-2 fixed inset-y-0 left-0">
     <a href="">
        <img src="../src/img/GRAPHIC.png" alt="Logo Raimuna Cabang Cimahi" class="w-48 mx-auto mb-6">
    </a>
    <nav>
        <ul class="space-y-2">
            <li><a href="dashboard_<?php echo $role; ?>.php" class="block py-2.5 px-4 rounded hover:bg-gray-700">Dashboard</a></li>
            <li><a href="data_berkas_kontingen.php" class="block py-2.5 px-4 rounded hover:bg-gray-700">Data Berkas Kontingen</a></li>
            <li><a href="data_unsur_kontingen.php" class="block py-2.5 px-4 rounded hover:bg-gray-700">Data Unsur Kontingen</a></li>
                <li><a href="bukti_pembayaran.php" class="block py-2.5 px-4 rounded bg-gray-700">Bukti Pembayaran</a></li>
                <li><a href="data_peserta.php" class="block py-2.5 px-4 rounded transition duration-200 hover:bg-gray-700 ">Data Peserta</a></li
            <li><a href="data_peserta_jamcab.php" class="block py-2.5 px-4 rounded hover:bg-gray-700">Data Peserta JamCab</a></li>
            <li><a href="../logout.php" class="block py-2.5 px-4 rounded hover:bg-gray-700">Logout</a></li>
        </ul>
    </nav>
</div>
    <div class="main-content flex-1 p-10 md:ml-64">
        <header class="bg-white shadow p-6 rounded-lg mb-6">
            <h1 class="text-3xl font-bold text-gray-800">Bukti Pembayaran</h1>
        </header>
        <section class="content bg-white shadow p-6 rounded-lg">
            <?php if ($message): ?><p class="text-green-600 mb-4"><?php echo $message; ?></p><?php endif; ?>
            <?php if ($error): ?><p class="text-red-600 mb-4"><?php echo $error; ?></p><?php endif; ?>

            <div class="summary-boxes flex justify-around mb-6 space-x-4">
                <div class="box bg-gray-50 p-6 rounded-lg shadow-md text-center flex-1">
                    <h3 class="text-lg font-semibold text-gray-700 mb-2">Total Peserta Terdaftar</h3>
                    <p class="text-2xl font-bold text-blue-600"><?php echo $total_peserta_terdaftar; ?> Orang</p>
                </div>
                <div class="box bg-gray-50 p-6 rounded-lg shadow-md text-center flex-1">
                    <h3 class="text-lg font-semibold text-gray-700 mb-2">Total Nominal Pembayaran</h3>
                    <p class="text-2xl font-bold text-blue-600">Rp. <?php echo number_format($total_nominal_pembayaran, 0, ',', '.'); ?></p>
                </div>
            </div>

            <button onclick="document.getElementById('addPembayaranModal').style.display='block'" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mb-4">Add Pembayaran</button>

            <div id="addPembayaranModal" class="modal">
                <div class="modal-content bg-white m-auto p-8 rounded-lg shadow-lg w-11/12 md:w-1/2 lg:w-1/3">
                    <span class="close-button text-gray-700 float-right text-4xl font-bold cursor-pointer hover:text-black" onclick="document.getElementById('addPembayaranModal').style.display='none'">&times;</span>
                    <h2 class="text-2xl font-bold mb-4">Form Add Bukti Pembayaran</h2>
                    <form action="bukti_pembayaran.php" method="POST" enctype="multipart/form-data" class="space-y-4">
                        <div>
                            <label for="jumlah_terdaftar" class="block text-gray-700 text-sm font-bold mb-2">Jumlah Terdaftar (Orang):</label>
                            <input type="number" id="jumlah_terdaftar" name="jumlah_terdaftar" min="1" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        </div>

                        <div>
                            <label for="nominal" class="block text-gray-700 text-sm font-bold mb-2">Nominal Pembayaran (Rp.):</label>
                            <input type="number" id="nominal" name="nominal" min="0" step="0.01" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        </div>

                        <div>
                            <label for="bukti_file" class="block text-gray-700 text-sm font-bold mb-2">Upload Bukti Pembayaran:</label>
                            <input type="file" id="bukti_file" name="bukti_file" accept="image/*,.pdf" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        </div>

                        <div class="flex justify-end space-x-2 mt-4">
                            <button type="submit" name="add_pembayaran" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">Save Changes</button>
                            <button type="button" onclick="document.getElementById('addPembayaranModal').style.display='none'" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">Close</button>
                        </div>
                    </form>
                </div>
            </div>

              <div class="flex flex-wrap gap-1 mb-1">
                        <input type="text" id="searchInput" placeholder="Cari nama berkas..." class="border p-2 rounded mb-4 w-full max-w-sm">
                         <button onclick="exportToExcel('berkasTable')" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 mb-4 gap-4">Export ke Excel</button>
                        <button onclick="exportToPDF()" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 mb-4 gap-4">Export ke PDF</button>
                        </div>
                        <script>
                        document.getElementById('searchInput').addEventListener('keyup', function () {
                            let value = this.value.toLowerCase();
                            document.querySelectorAll('tbody tr').forEach(row => {
                            row.style.display = row.innerText.toLowerCase().includes(value) ? '' : 'none';
                            });
                        });
                        </script>

            <div class="overflow-x-auto">
                <table class="min-w-full bg-white border border-gray-300">
                    <thead>
                        <tr class="bg-gray-200 text-gray-600 uppercase text-sm leading-normal">
                            <th class="py-3 px-6 text-left border-b border-gray-300">No</th>
                            <th class="py-3 px-6 text-left border-b border-gray-300">Jumlah Terdaftar</th>
                            <th class="py-3 px-6 text-left border-b border-gray-300">Nominal Pembayaran</th>
                            <th class="py-3 px-6 text-left border-b border-gray-300">Bukti File</th>
                            <th class="py-3 px-6 text-left border-b border-gray-300">Status</th>
                            <th class="py-3 px-6 text-left border-b border-gray-300">Tanggal Upload</th>
                            <th class="py-3 px-6 text-left border-b border-gray-300">Action</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-600 text-sm font-light">
                        <?php
                        if ($result_pembayaran->num_rows > 0) {
                            $no = 1;
                            while($row = $result_pembayaran->fetch_assoc()) {
                                echo "<tr class='border-b border-gray-200 hover:bg-gray-100'>";
                                echo "<td class='py-3 px-6 text-left whitespace-nowrap'>" . $no++ . "</td>";
                                echo "<td class='py-3 px-6 text-left'>" . htmlspecialchars($row['jumlah_terdaftar']) . "</td>";
                                echo "<td class='py-3 px-6 text-left'>Rp. " . number_format($row['nominal'], 0, ',', '.') . "</td>";
                                echo "<td class='py-3 px-6 text-left'><a href='../uploads/bukti_pembayaran/" . htmlspecialchars($row['bukti_pembayaran_path']) . "' target='_blank' class='text-blue-600 hover:underline'>Lihat Bukti</a></td>";
                                echo "<td class='py-3 px-6 text-left'>" . htmlspecialchars($row['status']) . "</td>";
                                echo "<td class='py-3 px-6 text-left'>" . date('d M Y H:i', strtotime($row['tanggal_upload'])) . "</td>";
                                echo "<td class='py-3 px-6 text-left whitespace-nowrap'>";
                                echo "<a href='bukti_pembayaran.php?delete_pembayaran_id=" . $row['id'] . "' class='bg-red-500 hover:bg-red-700 text-white font-bold py-1 px-3 rounded mr-2' onclick='return confirm(\"Yakin ingin menghapus bukti pembayaran ini?\");'>Hapus</a>";
                                // Tambahkan tombol Edit jika diperlukan:
                                echo " <a href='edit_pembayaran.php?id=" . $row['id'] . "' class='bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-1 px-3 rounded'>Edit</a>";
                                echo "</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='7' class='py-3 px-6 text-center'>Belum ada data pembayaran.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</body>
</html>