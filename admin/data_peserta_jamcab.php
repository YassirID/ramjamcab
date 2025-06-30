<?php
session_start();
include('../includes/db.php');

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'pinkonran' && $_SESSION['role'] !== 'pinkoncab')) {
    header("Location: ../index.php");
    exit();
}
$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];


// ==================================================
// HANDLE TAMBAH PESERTA
// ==================================================
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_peserta_jamcab']) && $role == 'pinkonran') {
    $nama_lengkap       = $conn->real_escape_string($_POST['nama_lengkap']);
    $jenis_kelamin      = $conn->real_escape_string($_POST['jenis_kelamin']);
    $no_hp_wa           = $conn->real_escape_string($_POST['no_hp_wa']);
    $agama              = $conn->real_escape_string($_POST['agama']);
    $riwayat_penyakit   = $conn->real_escape_string($_POST['riwayat_penyakit']);
    $tempat_lahir       = $conn->real_escape_string($_POST['tempat_lahir']);
    $tanggal_lahir      = $conn->real_escape_string($_POST['tanggal_lahir']);
    $ukuran_kaos        = $conn->real_escape_string($_POST['ukuran_kaos']);
    $golongan_darah     = $conn->real_escape_string($_POST['golongan_darah']);
    $kategori           = 'Penggalang';

    $sql = "INSERT INTO peserta_jamcab 
            (user_id, nama_lengkap, jenis_kelamin, no_hp_wa, agama, riwayat_penyakit, tempat_lahir, tanggal_lahir, ukuran_kaos, golongan_darah, kategori) 
            VALUES ('$user_id', '$nama_lengkap', '$jenis_kelamin', '$no_hp_wa', '$agama', '$riwayat_penyakit', '$tempat_lahir', '$tanggal_lahir', '$ukuran_kaos', '$golongan_darah', '$kategori')";

    if ($conn->query($sql) === TRUE) {
        $_SESSION['message'] = "Data peserta JamCab berhasil ditambahkan.";
    } else {
        $_SESSION['error'] = "Error: " . $conn->error;
    }
    header("Location: data_peserta_jamcab.php");
    exit();
}

// ==================================================
// HANDLE DELETE
// ==================================================
if (isset($_GET['delete_id']) && $role == 'pinkonran') {
    $id = $conn->real_escape_string($_GET['delete_id']);
    $sql = "DELETE FROM peserta_jamcab WHERE id = '$id' AND user_id = '$user_id'";
    if ($conn->query($sql) === TRUE) {
        $_SESSION['message'] = "Data peserta berhasil dihapus.";
    } else {
        $_SESSION['error'] = "Error deleting record: " . $conn->error;
    }
    header("Location: data_peserta_jamcab.php");
    exit();
}

// ==================================================
// AMBIL DATA
// ==================================================
if ($role == 'pinkoncab') {
    $sql_peserta = "SELECT p.*, u.username AS pinkonran_username 
                    FROM peserta_jamcab p 
                    JOIN users u ON p.user_id = u.id 
                    ORDER BY p.created_at DESC";
} else {
    $sql_peserta = "SELECT * FROM peserta_jamcab WHERE user_id = '$user_id' ORDER BY created_at DESC";
}
$result_peserta = $conn->query($sql_peserta);

// ==================================================
// NOTIFIKASI
// ==================================================
$message = $_SESSION['message'] ?? '';
$error = $_SESSION['error'] ?? '';
unset($_SESSION['message'], $_SESSION['error']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Data Peserta JamCab (Penggalang)</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex h-screen font-sans">

<!-- =========================== -->
<!--        SIDEBAR              -->
<!-- =========================== -->
<?php include 'sidebar.php'; ?>


<!-- =========================== -->
<!--       MAIN CONTENT           -->
<!-- =========================== -->
<div class="main-content flex-1 p-10 ml-64">
    <header class="bg-white rounded p-6 shadow">
        <h1 class="text-3xl font-bold text-gray-800">Data Peserta JamCab (Penggalang)</h1>
    </header>

    <section class="content bg-white rounded p-6 shadow mt-6">
        <?php if ($message): ?>
            <div class="bg-green-100 text-green-800 p-3 rounded"><?php echo $message; ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="bg-red-100 text-red-800 p-3 rounded"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if ($role == 'pinkonran'): ?>
            <!-- BUTTON TAMBAH PESERTA -->
            <button class="bg-blue-500 hover:bg-blue-600 text-white rounded px-4 py-2 mt-4"
                    onclick="document.getElementById('addModal').style.display='block'">Tambah Peserta Penggalang</button>
            
            <!-- MODAL TAMBAH PESERTA -->
            <div id="addModal" class="fixed inset-0 hidden bg-gray-900 bg-opacity-50  items-center justify-center">
                <div class="bg-white rounded-lg p-6 w-11/12 md:w-2/3">
                    <h2 class="text-xl font-bold mb-4">Tambah Peserta Penggalang</h2>
                    <form method="POST" class="space-y-4">
                        <input type="hidden" name="add_peserta_jamcab" value="1">
                        <div>
                            <label class="block font-bold">Nama Lengkap</label>
                            <input required name="nama_lengkap" class="border rounded w-full p-2">
                        </div>
                        <div>
                            <label class="block font-bold">Jenis Kelamin</label>
                            <select name="jenis_kelamin" class="border rounded w-full p-2">
                                <option value="Laki-laki">Laki-laki</option>
                                <option value="Perempuan">Perempuan</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-bold">No. HP / WA</label>
                            <input required name="no_hp_wa" class="border rounded w-full p-2">
                        </div>
                        <div>
                            <label class="block font-bold">Agama</label>
                            <input required name="agama" class="border rounded w-full p-2">
                        </div>
                        <div>
                            <label class="block font-bold">Riwayat Penyakit</label>
                            <input name="riwayat_penyakit" class="border rounded w-full p-2">
                        </div>
                        <div>
                            <label class="block font-bold">Tempat Lahir</label>
                            <input required name="tempat_lahir" class="border rounded w-full p-2">
                        </div>
                        <div>
                            <label class="block font-bold">Tanggal Lahir</label>
                            <input required type="date" name="tanggal_lahir" class="border rounded w-full p-2">
                        </div>
                        <div>
                            <label class="block font-bold">Ukuran Kaos</label>
                            <select required name="ukuran_kaos" class="border rounded w-full p-2">
                                <option value="">Pilih Ukuran</option>
                                <option>S</option><option>M</option><option>L</option><option>XL</option><option>XXL</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-bold">Golongan Darah</label>
                            <input required name="golongan_darah" class="border rounded w-full p-2">
                        </div>
                        <div class="flex justify-end space-x-2 mt-4">
                            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white rounded px-4 py-2">Simpan</button>
                            <button type="button" class="bg-gray-500 hover:bg-gray-600 text-white rounded px-4 py-2" 
                                    onclick="document.getElementById('addModal').style.display='none'">Batal</button>
                        </div>
                    </form>
                </div>
            </div>
        <?php endif; ?>

        <!-- TABLE PESERTA JAMCAB -->
         
        <div class="overflow-x-auto mt-6">
            <table class="min-w-full bg-white rounded border">
                <thead class="bg-gray-100">
                    <tr class="text-gray-600 uppercase text-sm">
                        <th class="p-3 text-left">No</th>
                        <?php if ($role == 'pinkoncab'): ?>
                        <th class="p-3 text-left">Email Pinkonran</th>
                        <?php endif; ?>
                        <th class="p-3 text-left">Nama Lengkap</th>
                        <th class="p-3 text-left">Jenis Kelamin</th>
                        <th class="p-3 text-left">No. HP / WA</th>
                        <th class="p-3 text-left">Kategori</th>
                        <th class="p-3 text-left">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $no = 1;
                    if ($result_peserta->num_rows > 0) {
                        while ($row = $result_peserta->fetch_assoc()) {
                            echo "<tr class='border-t hover:bg-gray-50'>";
                            echo "<td class='p-3'>{$no}</td>";
                            if ($role == 'pinkoncab') {
                                echo "<td class='p-3'>{$row['pinkonran_username']}</td>";
                            }
                            echo "<td class='p-3'>{$row['nama_lengkap']}</td>";
                            echo "<td class='p-3'>{$row['jenis_kelamin']}</td>";
                            echo "<td class='p-3'>{$row['no_hp_wa']}</td>";
                            echo "<td class='p-3'>{$row['kategori']}</td>";
                          if ($role == 'pinkonran'): ?>
<td class="p-3">
    <button class="bg-yellow-500 hover:bg-yellow-600 text-white rounded px-3 py-1"
            onClick='openEditModal(<?php echo json_encode($row); ?>)'>Edit</button>
    <a href='data_peserta_jamcab.php?delete_id=<?php echo $row["id"]; ?>' 
       class='bg-red-500 hover:bg-red-600 text-white rounded px-3 py-1'
       onClick='return confirm("Yakin menghapus peserta ini?");'>Hapus</a>
</td>
<?php endif;
                            echo "</td>";
                            echo "</tr>";
                            $no++;
                        }
                    } else {
                        echo "<tr><td colspan='" . ($role == 'pinkoncab' ? 7 : 6) . "' class='p-3 text-center'>Belum ada peserta Penggalang</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </section>
</div>
</body>
</html>
