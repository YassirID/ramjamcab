<?php
session_start();
include('../includes/db.php');
include('../includes/auth.php'); // tambahkan validasi login (jika kamu pakai sistem auth.php)

if (!in_array($_SESSION['role'], ['admin', 'cabang', 'selatan', 'utara', 'tengah', 'saka'])) {
    header("Location: ../index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];
$username = $_SESSION['username'];


// --- Handle Add Unsur Kontingen ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_unsur'])) {
    $nama_lengkap = $conn->real_escape_string($_POST['nama_lengkap']);
    $jenis_kelamin = $conn->real_escape_string($_POST['jenis_kelamin']);
    $no_hp_wa = $conn->real_escape_string($_POST['no_hp_wa']);
    $agama = $conn->real_escape_string($_POST['agama']);
    $riwayat_penyakit = $conn->real_escape_string($_POST['riwayat_penyakit']);
    $tempat_lahir = $conn->real_escape_string($_POST['tempat_lahir']);
    $tanggal_lahir = $conn->real_escape_string($_POST['tanggal_lahir']);
    $ukuran_kaos = $conn->real_escape_string($_POST['ukuran_kaos']);
    $kategori_unsur = $conn->real_escape_string($_POST['kategori_unsur']);
    $golongan_darah = $conn->real_escape_string($_POST['golongan_darah']);

    $sql = "INSERT INTO unsur_kontingen (user_id, nama_lengkap, jenis_kelamin, no_hp_wa, agama, riwayat_penyakit, tempat_lahir, tanggal_lahir, ukuran_kaos, kategori_unsur, golongan_darah)
            VALUES ('$user_id', '$nama_lengkap', '$jenis_kelamin', '$no_hp_wa', '$agama', '$riwayat_penyakit', '$tempat_lahir', '$tanggal_lahir', '$ukuran_kaos', '$kategori_unsur', '$golongan_darah')";

    if ($conn->query($sql) === TRUE) {
        $_SESSION['message'] = "Data unsur kontingen berhasil ditambahkan.";
        header("Location: data_unsur_kontingen.php");
        exit();
    } else {
        $_SESSION['error'] = "Error: " . $sql . "<br>" . $conn->error;
    }
}

// --- Handle Upload Berkas Pendukung (Pas Foto, KTA, dll.) ---
// Ini akan menjadi logika yang lebih kompleks karena ada 4 jenis file
// Untuk kesederhanaan, saya akan tunjukkan contoh upload satu file (pas_foto)
// Anda perlu mengulanginya untuk KTA, Asuransi, SFH
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['upload_berkas_unsur'])) {
    $unsur_id = $conn->real_escape_string($_POST['unsur_id']);
    $file_type = $conn->real_escape_string($_POST['file_type']); // e.g., 'pas_foto', 'kta', 'asuransi', 'sfh'

    $target_dir = "../uploads/berkas_unsur/";
    $file_name_prefix = $unsur_id . "_" . $file_type . "_";
    $uploaded_file_name = uniqid($file_name_prefix) . "_" . basename($_FILES["file_unsur"]["name"]);
    $target_file = $target_dir . $uploaded_file_name;
    $uploadOk = 1;
    $fileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    // Check file type
    $allowed_types = ['jpg', 'jpeg', 'png', 'pdf']; // Sesuaikan jenis file yang diizinkan
    if (!in_array($fileType, $allowed_types)) {
        $_SESSION['error'] = "Maaf, hanya file JPG, JPEG, PNG, dan PDF yang diizinkan untuk " . $file_type . ".";
        $uploadOk = 0;
    }

    if ($uploadOk == 0) {
        $_SESSION['error'] = $_SESSION['error'] ?? "Maaf, file Anda tidak terunggah.";
    } else {
        if (move_uploaded_file($_FILES["file_unsur"]["tmp_name"], $target_file)) {
            // Update database berdasarkan jenis file
            $sql_update_file = "";
            if ($file_type == 'pas_foto') $sql_update_file = "UPDATE unsur_kontingen SET pas_foto_path = '$uploaded_file_name' WHERE id = '$unsur_id' AND user_id = '$user_id'";
            if ($file_type == 'kta') $sql_update_file = "UPDATE unsur_kontingen SET kta_path = '$uploaded_file_name' WHERE id = '$unsur_id' AND user_id = '$user_id'";
            if ($file_type == 'asuransi') $sql_update_file = "UPDATE unsur_kontingen SET asuransi_path = '$uploaded_file_name' WHERE id = '$unsur_id' AND user_id = '$user_id'";
            if ($file_type == 'sfh') $sql_update_file = "UPDATE unsur_kontingen SET sertifikat_sfh_path = '$uploaded_file_name' WHERE id = '$unsur_id' AND user_id = '$user_id'";

            if ($conn->query($sql_update_file) === TRUE) {
                $_SESSION['message'] = ucfirst(str_replace('_', ' ', $file_type)) . " berhasil diunggah.";
            } else {
                $_SESSION['error'] = "Error updating database: " . $conn->error;
            }
        } else {
            $_SESSION['error'] = "Maaf, terjadi kesalahan saat mengunggah file Anda.";
        }
    }
    header("Location: data_unsur_kontingen.php");
    exit();
}


// --- Handle Edit Unsur Kontingen (Sama seperti Add, tapi dengan UPDATE) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_unsur'])) {
    $id = $conn->real_escape_string($_POST['unsur_id_edit']); // ID dari unsur yang diedit
    $nama_lengkap = $conn->real_escape_string($_POST['nama_lengkap_edit']);
    $jenis_kelamin = $conn->real_escape_string($_POST['jenis_kelamin_edit']);
    $no_hp_wa = $conn->real_escape_string($_POST['no_hp_wa_edit']);
    $agama = $conn->real_escape_string($_POST['agama_edit']);
    $riwayat_penyakit = $conn->real_escape_string($_POST['riwayat_penyakit_edit']);
    $tempat_lahir = $conn->real_escape_string($_POST['tempat_lahir_edit']);
    $tanggal_lahir = $conn->real_escape_string($_POST['tanggal_lahir_edit']);
    $ukuran_kaos = $conn->real_escape_string($_POST['ukuran_kaos_edit']);
    $kategori_unsur = $conn->real_escape_string($_POST['kategori_unsur_edit']);
    $golongan_darah = $conn->real_escape_string($_POST['golongan_darah_edit']);

    $sql = "UPDATE unsur_kontingen SET
                nama_lengkap = '$nama_lengkap',
                jenis_kelamin = '$jenis_kelamin',
                no_hp_wa = '$no_hp_wa',
                agama = '$agama',
                riwayat_penyakit = '$riwayat_penyakit',
                tempat_lahir = '$tempat_lahir',
                tanggal_lahir = '$tanggal_lahir',
                ukuran_kaos = '$ukuran_kaos',
                kategori_unsur = '$kategori_unsur',
                golongan_darah = '$golongan_darah'
            WHERE id = '$id' AND user_id = '$user_id'";

    if ($conn->query($sql) === TRUE) {
        $_SESSION['message'] = "Data unsur kontingen berhasil diperbarui.";
    } else {
        $_SESSION['error'] = "Error updating record: " . $conn->error;
    }
    header("Location: data_unsur_kontingen.php");
    exit();
}


// --- Handle Delete Unsur Kontingen ---
if (isset($_GET['delete_unsur_id'])) {
    $id_to_delete = $conn->real_escape_string($_GET['delete_unsur_id']);

    // Hapus file fisik terkait sebelum menghapus dari database
    $sql_get_files = "SELECT pas_foto_path, kta_path, asuransi_path, sertifikat_sfh_path FROM unsur_kontingen WHERE id = '$id_to_delete' AND user_id = '$user_id'";
    $result_files = $conn->query($sql_get_files);
    if ($result_files->num_rows > 0) {
        $row_files = $result_files->fetch_assoc();
        $upload_dir = "../uploads/berkas_unsur/";
        if ($row_files['pas_foto_path'] && file_exists($upload_dir . $row_files['pas_foto_path'])) unlink($upload_dir . $row_files['pas_foto_path']);
        if ($row_files['kta_path'] && file_exists($upload_dir . $row_files['kta_path'])) unlink($upload_dir . $row_files['kta_path']);
        if ($row_files['asuransi_path'] && file_exists($upload_dir . $row_files['asuransi_path'])) unlink($upload_dir . $row_files['asuransi_path']);
        if ($row_files['sertifikat_sfh_path'] && file_exists($upload_dir . $row_files['sertifikat_sfh_path'])) unlink($upload_dir . $row_files['sertifikat_sfh_path']);
    }

    $sql_delete = "DELETE FROM unsur_kontingen WHERE id = '$id_to_delete' AND user_id = '$user_id'";
    if ($conn->query($sql_delete) === TRUE) {
        $_SESSION['message'] = "Data unsur kontingen berhasil dihapus.";
    } else {
        $_SESSION['error'] = "Error deleting record: " . $conn->error;
    }
    header("Location: data_unsur_kontingen.php");
    exit();
}


$filter_kontingen = '';
if (in_array($role, ['admin', 'cabang']) && isset($_GET['role'])) {
    $filter_kontingen = $conn->real_escape_string($_GET['role']);
}


$sql_unsur = "SELECT u.*, us.username, us.role 
              FROM unsur_kontingen u 
              JOIN users us ON u.user_id = us.id 
              WHERE 1=1";


if (in_array($role, ['admin', 'cabang']) && $filter_kontingen != '') {
    $sql_unsur .= " AND us.role = '$filter_kontingen'";
}



$result_unsur = $conn->query($sql_unsur);
if (!$result_unsur) {
    die("Query Error: " . $conn->error);
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
    <title>Data Unsur Kontingen</title>
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
<?php include 'sidebar.php'; ?>

    <div class="main-content flex-1 p-10 md:ml-64">
        <header class="bg-white shadow p-6 rounded-lg mb-6">
            <h1 class="text-3xl font-bold text-gray-800">Data Unsur Kontingen</h1>
        </header>
        <section class="content bg-white shadow p-6 rounded-lg">
            <?php if ($message): ?><p class="text-green-600 mb-4"><?php echo $message; ?></p><?php endif; ?>
            <?php if ($error): ?><p class="text-red-600 mb-4"><?php echo $error; ?></p><?php endif; ?>

                <?php if (in_array($role, ['admin', 'selatan', 'utara', 'tengah', 'saka'])): ?>
                <button onclick="document.getElementById('addUnsurModal').style.display='block'" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mb-4">Add Unsur</button>
                <?php endif; ?>

           

            <!-- Add Form Start -->
<div id="addUnsurModal" class="modal">
  <div class="modal-content bg-white w-full max-w-7xl md:m-auto m-5 p-8 rounded-lg shadow-lg overflow-y-auto max-h-[90vh]">
    <span class="close-button text-gray-700 float-right text-4xl font-bold cursor-pointer hover:text-black"
      onclick="document.getElementById('addUnsurModal').style.display='none'">&times;</span>

    <h2 class="text-2xl font-bold mb-6">Form Add Data Unsur Kontingen</h2>

    <form action="data_unsur_kontingen.php" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-6">

      <!-- Kolom Kiri (6 input) -->
      <div class="space-y-4">
        <div>
          <label for="nama_lengkap" class="block text-sm font-bold text-gray-700 mb-1">Nama Lengkap:</label>
          <input type="text" id="nama_lengkap" name="nama_lengkap" required
            class="w-full border rounded px-3 py-2 shadow focus:outline-none">
        </div>

        <div>
          <label for="kontingen" class="block text-sm font-bold text-gray-700 mb-1">Kontingen:</label>
          <select id="kontingen" name="kontingen" required
            class="w-full border rounded px-3 py-2 shadow focus:outline-none">
            <option value="">Pilih Kontingen</option>
            <option value="utara">Utara</option>
            <option value="selatan">Selatan</option>
            <option value="tengah">Tengah</option>
            <option value="saka">Saka</option>
            <option value="cabang">Cabang</option>
          </select>
        </div>

        <div>
          <label for="jenis_kelamin" class="block text-sm font-bold text-gray-700 mb-1">Jenis Kelamin:</label>
          <select id="jenis_kelamin" name="jenis_kelamin" required
            class="w-full border rounded px-3 py-2 shadow focus:outline-none">
            <option value="">Pilih</option>
            <option value="Laki-laki">Laki-laki</option>
            <option value="Perempuan">Perempuan</option>
          </select>
        </div>

        <div>
          <label for="no_hp_wa" class="block text-sm font-bold text-gray-700 mb-1">No. HP/WA:</label>
          <input type="text" id="no_hp_wa" name="no_hp_wa"
            class="w-full border rounded px-3 py-2 shadow focus:outline-none">
        </div>

        <div>
          <label for="agama" class="block text-sm font-bold text-gray-700 mb-1">Agama:</label>
          <input type="text" id="agama" name="agama"
            class="w-full border rounded px-3 py-2 shadow focus:outline-none">
        </div>

        <div>
          <label for="riwayat_penyakit" class="block text-sm font-bold text-gray-700 mb-1">Riwayat Penyakit:</label>
          <textarea id="riwayat_penyakit" name="riwayat_penyakit"
            class="w-full border rounded px-3 py-2 shadow focus:outline-none"></textarea>
        </div>
      </div>

      <!-- Kolom Kanan (5 input) -->
      <div class="space-y-4">
        <div>
          <label for="tempat_lahir" class="block text-sm font-bold text-gray-700 mb-1">Tempat Lahir:</label>
          <input type="text" id="tempat_lahir" name="tempat_lahir"
            class="w-full border rounded px-3 py-2 shadow focus:outline-none">
        </div>

        <div>
          <label for="tanggal_lahir" class="block text-sm font-bold text-gray-700 mb-1">Tanggal Lahir:</label>
          <input type="date" id="tanggal_lahir" name="tanggal_lahir"
            class="w-full border rounded px-3 py-2 shadow focus:outline-none">
        </div>

        <div>
          <label for="ukuran_kaos" class="block text-sm font-bold text-gray-700 mb-1">Ukuran Kaos:</label>
          <select id="ukuran_kaos" name="ukuran_kaos"
            class="w-full border rounded px-3 py-2 shadow focus:outline-none">
            <option value="">Pilih</option>
            <option value="S">S</option>
            <option value="M">M</option>
            <option value="L">L</option>
            <option value="XL">XL</option>
            <option value="XXL">XXL</option>
          </select>
        </div>

        <div>
          <label for="kategori_unsur" class="block text-sm font-bold text-gray-700 mb-1">Kategori Unsur:</label>
          <input type="text" id="kategori_unsur" name="kategori_unsur" placeholder="Ex: Pembina Pendamping" required
            class="w-full border rounded px-3 py-2 shadow focus:outline-none">
        </div>

        <div>
          <label for="golongan_darah" class="block text-sm font-bold text-gray-700 mb-1">Golongan Darah:</label>
          <input type="text" id="golongan_darah" name="golongan_darah"
            class="w-full border rounded px-3 py-2 shadow focus:outline-none">
        </div>
      </div>

      <!-- Tombol -->
      <div class="col-span-1 md:col-span-2 flex justify-end gap-2 pt-4">
        <button type="submit" name="add_unsur"
          class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none">
          Save Changes
        </button>
        <button type="button"
          onclick="document.getElementById('addUnsurModal').style.display='none'"
          class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded focus:outline-none">
          Close
        </button>
      </div>
    </form>
  </div>
</div>
        <!-- Add Form End -->
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

                        <!-- Table Start -->
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white border border-gray-300">
                    <thead>
                        <tr class="bg-gray-200 text-gray-600 uppercase text-sm leading-normal">
                            <th class="py-3 px-6 text-left border-b border-gray-300">No</th>
                            <th class="py-3 px-6 text-left border-b border-gray-300">Nama Lengkap</th>
                            <th class="py-3 px-6 text-left border-b border-gray-300">Jenis Kelamin</th>
                            <th class="py-3 px-6 text-left border-b border-gray-300">No. Hp/WA</th>
                            <th class="py-3 px-6 text-left border-b border-gray-300">Kategori</th>
                            <th class="py-3 px-6 text-left border-b border-gray-300">Aksi</th>
                            <th class="py-3 px-6 text-left border-b border-gray-300">Berkas Pendukung</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-600 text-sm font-light">
                        <?php
$can_edit_delete = false;
if ($role == 'admin') {
    $can_edit_delete = true;
} elseif (in_array($role, ['selatan', 'utara', 'tengah', 'saka']) && $row['user_id'] == $user_id) {
    $can_edit_delete = true;
}
?>

                        <?php
                        if ($result_unsur->num_rows > 0) {
                            $no = 1;
                            while($row = $result_unsur->fetch_assoc()) {
                                echo "<tr class='border-b border-gray-200 hover:bg-gray-100'>";
                                echo "<td class='py-3 px-6 text-left whitespace-nowrap'>" . $no++ . "</td>";
                                echo "<td class='py-3 px-6 text-left'>" . ($row['nama_lengkap']) . "</td>";
                                echo "<td class='py-3 px-6 text-left'>" . ($row['jenis_kelamin']) . "</td>";
                                echo "<td class='py-3 px-6 text-left'>" . ($row['no_hp_wa']) . "</td>";
                                echo "<td class='py-3 px-6 text-left'>" . ($row['kategori_unsur']) . "</td>";
                                echo "<td class='py-3 px-6 text-left whitespace-nowrap'>";
                               if ($can_edit_delete): 
                                echo "<button class='bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-1 px-3 rounded mr-2' onclick='openEditUnsurModal(" . json_encode($row) . ")'>Edit</button>";
                                echo "<a href='data_unsur_kontingen.php?delete_unsur_id=" . $row['id'] . "' class='bg-red-500 hover:bg-red-700 text-white font-bold py-1 px-3 rounded' onclick='return confirm(\"Yakin ingin menghapus data ini?\");'>Delete</a>";
                                
                                endif; 
                                echo "</td>";
                               echo "<td class='py-3 px-6 text-left text-xs flex flex-col space-y-1'>";

                                $berkasList = [
                                    'pas_foto' => 'Pas Foto',
                                    'kta' => 'KTA',
                                    'asuransi' => 'Asuransi',
                                    'sertifikat_sfh' => 'Sertifikat SFH'
                                ];

                                foreach ($berkasList as $field => $label) {
                                    $filePath = $row[$field . '_path'];

                                    echo "<div class='flex items-center space-x-2'>";
                                    echo "<span class='font-semibold'>{$label}:</span> ";

                                    if ($filePath) {
                                        // Jika file sudah diupload
                                        echo "<a href='../uploads/berkas_unsur/{$filePath}' target='_blank' class='text-blue-600 hover:underline'>Lihat</a> ";
                                        echo "<a href='berkas_unsur_handler.php?action=delete&id={$row['id']}&type=$field' class='text-red-500 hover:text-red-700' 
                                                title='Hapus' 
                                                onClick='return confirm(\"Yakin hapus {$label}?\")'>Hapus</a> ";
                                        echo "<button onClick='openUploadModal({$row['id']}, \"{$field}\")' 
                                                    class='bg-green-500 hover:bg-green-600 text-white py-0.5 px-2 rounded text-xs'>Ganti</button>";
                                    } else {
                                        // Jika belum diupload
                                        echo "<span class='text-gray-500'>Belum ada</span> ";
                                        echo "<button onClick='openUploadModal({$row['id']}, \"{$field}\")' 
                                                    class='bg-yellow-500 hover:bg-yellow-600 text-white py-0.5 px-2 rounded text-xs'>Upload</button>";
                                    }

                                    echo "</div>";
                                }

                                echo "</td>";

                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='7' class='py-3 px-6 text-center'>Belum ada data unsur kontingen.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
            <!-- Table End -->

<!-- Edit Form Start -->
<div id="editUnsurModal" class="modal">
  <div class="modal-content bg-white w-full max-w-7xl md:m-auto m-5 p-8 rounded-lg shadow-lg overflow-y-auto max-h-[90vh]">
    <span class="close-button text-gray-700 float-right text-4xl font-bold cursor-pointer hover:text-black"
      onclick="document.getElementById('editUnsurModal').style.display='none'">&times;</span>

    <h2 class="text-2xl font-bold mb-6">Form Edit Data Unsur Kontingen</h2>

    <form action="data_unsur_kontingen.php" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <!-- Hidden ID -->
      <input type="hidden" id="unsur_id_edit" name="unsur_id_edit">

      <!-- Kolom Kiri -->
      <div class="space-y-4">
        <div>
          <label for="nama_lengkap_edit" class="block text-sm font-bold text-gray-700 mb-1">Nama Lengkap:</label>
          <input type="text" id="nama_lengkap_edit" name="nama_lengkap_edit" required
            class="w-full border rounded px-3 py-2 shadow focus:outline-none">
        </div>

        <div>
          <label for="kontingen_edit" class="block text-sm font-bold text-gray-700 mb-1">Kontingen:</label>
          <select id="kontingen_edit" name="kontingen" required
            class="w-full border rounded px-3 py-2 shadow focus:outline-none">
            <option value="">Pilih Kontingen</option>
            <option value="utara">Utara</option>
            <option value="selatan">Selatan</option>
            <option value="tengah">Tengah</option>
            <option value="saka">Saka</option>
            <option value="cabang">Cabang</option>
          </select>
        </div>

        <div>
          <label for="jenis_kelamin_edit" class="block text-sm font-bold text-gray-700 mb-1">Jenis Kelamin:</label>
          <select id="jenis_kelamin_edit" name="jenis_kelamin_edit" required
            class="w-full border rounded px-3 py-2 shadow focus:outline-none">
            <option value="">Pilih</option>
            <option value="Laki-laki">Laki-laki</option>
            <option value="Perempuan">Perempuan</option>
          </select>
        </div>

        <div>
          <label for="no_hp_wa_edit" class="block text-sm font-bold text-gray-700 mb-1">No. HP/WA:</label>
          <input type="text" id="no_hp_wa_edit" name="no_hp_wa_edit"
            class="w-full border rounded px-3 py-2 shadow focus:outline-none">
        </div>

        <div>
          <label for="agama_edit" class="block text-sm font-bold text-gray-700 mb-1">Agama:</label>
          <input type="text" id="agama_edit" name="agama_edit"
            class="w-full border rounded px-3 py-2 shadow focus:outline-none">
        </div>

        <div>
          <label for="riwayat_penyakit_edit" class="block text-sm font-bold text-gray-700 mb-1">Riwayat Penyakit:</label>
          <textarea id="riwayat_penyakit_edit" name="riwayat_penyakit_edit"
            class="w-full border rounded px-3 py-2 shadow focus:outline-none"></textarea>
        </div>
      </div>

      <!-- Kolom Kanan -->
      <div class="space-y-4">
        <div>
          <label for="tempat_lahir_edit" class="block text-sm font-bold text-gray-700 mb-1">Tempat Lahir:</label>
          <input type="text" id="tempat_lahir_edit" name="tempat_lahir_edit"
            class="w-full border rounded px-3 py-2 shadow focus:outline-none">
        </div>

        <div>
          <label for="tanggal_lahir_edit" class="block text-sm font-bold text-gray-700 mb-1">Tanggal Lahir:</label>
          <input type="date" id="tanggal_lahir_edit" name="tanggal_lahir_edit"
            class="w-full border rounded px-3 py-2 shadow focus:outline-none">
        </div>

        <div>
          <label for="ukuran_kaos_edit" class="block text-sm font-bold text-gray-700 mb-1">Ukuran Kaos:</label>
          <select id="ukuran_kaos_edit" name="ukuran_kaos_edit"
            class="w-full border rounded px-3 py-2 shadow focus:outline-none">
            <option value="">Pilih</option>
            <option value="S">S</option>
            <option value="M">M</option>
            <option value="L">L</option>
            <option value="XL">XL</option>
            <option value="XXL">XXL</option>
          </select>
        </div>

        <div>
          <label for="kategori_unsur_edit" class="block text-sm font-bold text-gray-700 mb-1">Kategori Unsur:</label>
          <input type="text" id="kategori_unsur_edit" name="kategori_unsur_edit" required
            class="w-full border rounded px-3 py-2 shadow focus:outline-none">
        </div>

        <div>
          <label for="golongan_darah_edit" class="block text-sm font-bold text-gray-700 mb-1">Golongan Darah:</label>
          <input type="text" id="golongan_darah_edit" name="golongan_darah_edit"
            class="w-full border rounded px-3 py-2 shadow focus:outline-none">
        </div>
      </div>

      <!-- Tombol -->
      <div class="col-span-1 md:col-span-2 flex justify-end gap-2 pt-4">
        <button type="submit" name="edit_unsur"
          class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none">
          Save Changes
        </button>
        <button type="button"
          onclick="document.getElementById('editUnsurModal').style.display='none'"
          class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded focus:outline-none">
          Close
        </button>
      </div>
    </form>
  </div>
</div>
<!-- Edit Form End -->

            <div id="uploadBerkasModal" class="modal">
                <div class="modal-content bg-white m-auto p-8 rounded-lg shadow-lg w-11/12 md:w-1/2 lg:w-1/3">
                    <span class="close-button text-gray-700 float-right text-4xl font-bold cursor-pointer hover:text-black" onclick="document.getElementById('uploadBerkasModal').style.display='none'">&times;</span>
                    <h2 id="uploadModalTitle" class="text-2xl font-bold mb-4">Upload Berkas</h2>
                    <form action="data_unsur_kontingen.php" method="POST" enctype="multipart/form-data" class="space-y-4">
                        <input type="hidden" id="upload_unsur_id" name="unsur_id">
                        <input type="hidden" id="upload_file_type" name="file_type">
                        <div>
                            <label for="file_unsur" class="block text-gray-700 text-sm font-bold mb-2">Pilih File:</label>
                            <input type="file" id="file_unsur" name="file_unsur" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        </div>
                        <div class="flex justify-end space-x-2 mt-4">
                            <button type="submit" name="upload_berkas_unsur" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">Upload</button>
                            <button type="button" onclick="document.getElementById('uploadBerkasModal').style.display='none'" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">Batal</button>
                        </div>
                    </form>
                </div>
            </div>

        </section>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

<script>
function exportToExcel(tableID, filename = 'Data_Unsur_Kontingen.xlsx') {
  const table = document.getElementById(tableID);
  const wb = XLSX.utils.table_to_book(table, { sheet: "Sheet1" });
  XLSX.writeFile(wb, filename);
}
</script>


<script>
async function exportToPDF() {
  const { jsPDF } = window.jspdf;
  const doc = new jsPDF();

  doc.text("Data Unsur Kontingen", 14, 15);

  const table = document.getElementById("berkasTable");
  const headers = Array.from(table.querySelectorAll("thead th")).map(th => th.innerText);
  const rows = Array.from(table.querySelectorAll("tbody tr")).map(tr => {
    return Array.from(tr.querySelectorAll("td")).map(td => td.innerText.trim());
  });

  doc.autoTable({
    head: [headers],
    body: rows,
    startY: 20,
    theme: 'striped'
  });

  doc.save("Data_Berkas_Kontingen.pdf");
}
</script>


  <script>
    function openEditUnsurModal(data) {
        document.getElementById('unsur_id_edit').value = data.id;
        document.getElementById('nama_lengkap_edit').value = data.nama_lengkap;
        document.getElementById('jenis_kelamin_edit').value = data.jenis_kelamin;
        document.getElementById('no_hp_wa_edit').value = data.no_hp_wa;
        document.getElementById('agama_edit').value = data.agama;
        document.getElementById('riwayat_penyakit_edit').value = data.riwayat_penyakit;
        document.getElementById('tempat_lahir_edit').value = data.tempat_lahir;
        document.getElementById('tanggal_lahir_edit').value = data.tanggal_lahir;
        document.getElementById('ukuran_kaos_edit').value = data.ukuran_kaos;
        document.getElementById('kategori_unsur_edit').value = data.kategori_unsur;
        document.getElementById('golongan_darah_edit').value = data.golongan_darah;

        // Tambahkan ini untuk kontingen
        if (document.getElementById('kontingen_edit')) {
            document.getElementById('kontingen_edit').value = data.kontingen;
        }

        document.getElementById('editUnsurModal').style.display = 'block';
    }

    function openUploadModal(unsurId, fileType) {
        document.getElementById('upload_unsur_id').value = unsurId;
        document.getElementById('upload_file_type').value = fileType;
        let title = fileType.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
        document.getElementById('uploadModalTitle').innerText = 'Upload ' + title;
        document.getElementById('uploadBerkasModal').style.display = 'block';
    }
</script>

</body>
</html>