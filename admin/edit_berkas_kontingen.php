<?php
session_start();
include('../includes/db.php');

// Cek autentikasi
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'utara', 'tengah', 'selatan', 'saka'])) {
    $_SESSION['prg_error'] = "Anda tidak memiliki akses ke halaman ini.";
    header("Location: ../index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// Ambil ID dari URL
if (!isset($_GET['id'])) {
    $_SESSION['prg_error'] = "ID tidak ditemukan.";
    header("Location: data_berkas_kontingen.php");
    exit();
}

$id = $conn->real_escape_string($_GET['id']);

// Ambil data lama
if ($role === 'admin') {
    $sql = "SELECT * FROM berkas_kontingen WHERE id = '$id'";
} else {
    $sql = "SELECT * FROM berkas_kontingen WHERE id = '$id' AND user_id = '$user_id'";
}

$result = $conn->query($sql);
if ($result->num_rows !== 1) {
    $_SESSION['prg_error'] = "Data tidak ditemukan.";
    header("Location: data_berkas_kontingen.php");
    exit();
}
$data = $result->fetch_assoc();

// Proses Update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_berkas'])) {
    $nama_berkas = $conn->real_escape_string($_POST['nama_berkas']);
    $catatan_dokumen = $conn->real_escape_string($_POST['catatan_dokumen']);
    $status = $_POST['status'];
    $kontingen = $conn->real_escape_string($_POST['kontingen']);
    if ($status !== 'terkirim' && $status !== 'tertunda') {
        $status = 'tertunda';
    }

if ($role === 'admin') {
    $sql_update = "UPDATE berkas_kontingen 
                   SET nama_berkas = '$nama_berkas', 
                       catatan_dokumen = '$catatan_dokumen',
                       status = '$status',
                       kontingen = '$kontingen'
                   WHERE id = '$id'";
} else {
    $sql_update = "UPDATE berkas_kontingen 
                   SET nama_berkas = '$nama_berkas', 
                       catatan_dokumen = '$catatan_dokumen',
                       status = '$status',
                       kontingen = '$kontingen'
                   WHERE id = '$id' AND user_id = '$user_id'";
}



    if ($conn->query($sql_update) === TRUE) {
        $_SESSION['prg_message'] = "Data berhasil diperbarui.";
    } else {
        $_SESSION['prg_error'] = "Gagal memperbarui data.";
    }
    header("Location: data_berkas_kontingen.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Berkas Kontingen</title>
    <script src="https://cdn.tailwindcss.com"></script>
    </head>
<body class="font-sans bg-gray-100 p-5 flex items-center justify-center min-h-screen">

<div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-md">
    <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">Edit Data Berkas</h2>

    <form action="" method="POST" class="space-y-4" onsubmit="showLoader()">
        <div>
            <label for="nama_berkas" class="block text-gray-700 text-sm font-bold mb-2">Nama Berkas:</label>
            <input type="text" id="nama_berkas" name="nama_berkas" value="<?= htmlspecialchars($data['nama_berkas']) ?>" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
        </div>

        <div>
            <label for="catatan_dokumen" class="block text-gray-700 text-sm font-bold mb-2">Catatan Dokumen:</label>
            <input type="text" id="catatan_dokumen" name="catatan_dokumen" value="<?= htmlspecialchars($data['catatan_dokumen']) ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
        </div>
        <div>
            <label for="kontingen" class="block text-gray-700 text-sm font-bold mb-2">Kontingen:</label>
            <select name="kontingen" id="kontingen" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                <option value="">Pilih Kontingen</option>
                <option value="utara" <?= $data['kontingen'] === 'utara' ? 'selected' : '' ?>>Utara</option>
                <option value="tengah" <?= $data['kontingen'] === 'tengah' ? 'selected' : '' ?>>Tengah</option>
                <option value="selatan" <?= $data['kontingen'] === 'selatan' ? 'selected' : '' ?>>Selatan</option>
                <option value="saka" <?= $data['kontingen'] === 'saka' ? 'selected' : '' ?>>Saka</option>
            </select>
        </div>


        <div>
            <label for="status" class="block text-gray-700 text-sm font-bold mb-2">Status:</label>
            <select name="status" id="status" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                <option value="terkirim" <?= $data['status'] === 'terkirim' ? 'selected' : '' ?>>Terkirim</option>
                <option value="tertunda" <?= $data['status'] === 'tertunda' ? 'selected' : '' ?>>Tertunda</option>
            </select>
        </div>

        <div class="flex justify-end space-x-2 pt-4">
            <button type="submit" name="update_berkas" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">Simpan Perubahan</button>
            <a href="data_berkas_kontingen.php" class="bg-gray-400 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">Batal</a>
        </div>
    </form>
</div>

<div id="loader" class="hidden fixed inset-0 items-center justify-center bg-black bg-opacity-50 z-50">
  <div class="animate-spin rounded-full h-16 w-16 border-t-4 border-blue-500"></div>
</div>

<script>
  function showLoader() {
    document.getElementById('loader').classList.remove('hidden');
  }
</script>

</body>
</html>