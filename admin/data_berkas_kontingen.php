<?php
session_start();
include('../includes/db.php');

// Cek autentikasi
if (!in_array($_SESSION['role'], ['admin', 'cabang', 'selatan', 'utara', 'tengah', 'saka'])) {
    header("Location: ../index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// Handle upload (POST) - hanya admin, selatan, utara, tengah, saka yang bisa unggah
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_berkas'])) {
    if ($role === 'cabang') {
        $_SESSION['prg_error'] = "Role cabang tidak diperbolehkan menambahkan data.";
        header("Location: data_berkas_kontingen.php");
        exit();
    }
if ($role === 'cabang') {
    $_SESSION['prg_error'] = "Role cabang tidak diperbolehkan menambahkan data.";
    header("Location: data_berkas_kontingen.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_berkas']) && in_array($role, ['admin', 'selatan', 'utara', 'tengah', 'saka'])) {
    $nama_berkas = $conn->real_escape_string($_POST['nama_berkas']);
    $catatan_dokumen = $conn->real_escape_string($_POST['catatan_dokumen'] ?? '');
    $kontingen = $conn->real_escape_string($_POST['kontingen']);
    $status = $_POST['status'] === 'terkirim' ? 'terkirim' : 'tertunda';
    $uploaded_at = date('Y-m-d H:i:s');

    $target_dir = "../uploads/berkas_kontingen/";
    $file_name = uniqid() . "_" . basename($_FILES["file_berkas"]["name"]);
    $target_file = $target_dir . $file_name;
    $fileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);

    if (file_exists($target_file)) {
        $_SESSION['prg_error'] = "Maaf, file sudah ada.";
    } elseif (!in_array($fileType, ['pdf', 'docx', 'jpg', 'png'])) {
        $_SESSION['prg_error'] = "Format file tidak diizinkan (PDF, DOCX, JPG, PNG).";
    } elseif (move_uploaded_file($_FILES["file_berkas"]["tmp_name"], $target_file)) {
        $sql = "INSERT INTO berkas_kontingen 
    (user_id, nama_berkas, file_path, status, catatan_dokumen, kontingen, post_by, uploaded_at) 
    VALUES 
    ('$user_id', '$nama_berkas', '$file_name', '$status', '$catatan_dokumen', '$kontingen', '$role', '$uploaded_at')";

        $_SESSION['prg_message'] = $conn->query($sql) ? "Berkas berhasil diunggah." : "Gagal menyimpan ke database. {$conn->error}";
    } else {
        $_SESSION['prg_error'] = "Terjadi kesalahan saat mengunggah file.";
    }

    header("Location: data_berkas_kontingen.php");
    exit();
}
}


// Handle delete - hanya boleh hapus jika berkas milik sendiri
if (isset($_GET['delete_id'])) {
    $id_to_delete = $conn->real_escape_string($_GET['delete_id']);
    $sql_get_file = "SELECT file_path FROM berkas_kontingen WHERE id = '$id_to_delete' AND user_id = '$user_id'";
    $result_get_file = $conn->query($sql_get_file);

    if ($result_get_file && $result_get_file->num_rows > 0) {
        $row_file = $result_get_file->fetch_assoc();
        $file_to_delete = "../uploads/berkas_kontingen/" . $row_file['file_path'];
        if (file_exists($file_to_delete)) unlink($file_to_delete);

        $sql_delete = "DELETE FROM berkas_kontingen WHERE id = '$id_to_delete' AND user_id = '$user_id'";
        $_SESSION['prg_message'] = $conn->query($sql_delete) ? "Berkas berhasil dihapus." : "Gagal menghapus berkas.";
    } else {
        $_SESSION['prg_error'] = "Berkas tidak ditemukan.";
    }
}

// Pagination setup
$limit = 20; // jumlah data per halaman
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Filter post_by (khusus admin & cabang)
$post_by_filter = '';
if (in_array($role, ['admin', 'cabang']) && isset($_GET['filter_post_by']) && $_GET['filter_post_by'] !== '') {
    $post_by_filter = $conn->real_escape_string($_GET['filter_post_by']);
}


$post_by_filter = '';
if (in_array($role, ['admin', 'cabang']) && isset($_GET['filter_post_by']) && $_GET['filter_post_by'] !== '') {
    $post_by_filter = $conn->real_escape_string($_GET['filter_post_by']);
}

$conditions = [];

if (!in_array($role, ['admin', 'cabang'])) {
    $conditions[] = "post_by = '$role'";
} elseif ($post_by_filter !== '') {
    $conditions[] = "post_by = '$post_by_filter'";
}

$whereClause = '';
if (!empty($conditions)) {
    $whereClause = 'WHERE ' . implode(' AND ', $conditions);
}

$sql_berkas = "SELECT * FROM berkas_kontingen $whereClause ORDER BY uploaded_at DESC LIMIT $limit OFFSET $offset";
$sql_count  = "SELECT COUNT(*) as total FROM berkas_kontingen $whereClause";


$result_berkas = $conn->query($sql_berkas);
$result_count = $conn->query($sql_count);
$total_rows = $result_count->fetch_assoc()['total'] ?? 0;
$total_pages = ceil($total_rows / $limit);

?>



<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Berkas Kontingen</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Gaya dasar untuk modal yang mungkin tidak sepenuhnya bisa digantikan oleh utilitas Tailwind saja */
        .modal {
            display: none; 
            position: fixed; 
            z-index: 50; 
            left: 0;
            top: 0;
            width: 100%; 
            height: 100%; 
            overflow: auto;
            background-color: rgba(0,0,0,0.4); 
        }
    </style>
</head>
<body class="font-sans bg-gray-100 flex h-screen">
<?php include 'sidebar.php'; ?>

    <div class="main-content flex-1 p-10 md:ml-64">
        <header class="bg-white shadow p-6 rounded-lg mb-6">
            <h1 class="text-3xl font-bold text-gray-800">Data Berkas Kontingen</h1>
        </header>
        <section class="content bg-white shadow p-6 rounded-lg">
             <?php if (!in_array($role, ['cabang'])): ?>
            <button onclick="document.getElementById('addBerkasModal').style.display='block'" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mb-4">Add Berkas</button>
            <?php endif; ?>


            <div id="addBerkasModal" class="modal">
                <div class="modal-content bg-white m-auto p-8 rounded-lg shadow-lg w-11/12 md:w-1/2 lg:w-1/3">
                    <span class="close-button text-gray-700 float-right text-4xl font-bold cursor-pointer hover:text-black" onclick="document.getElementById('addBerkasModal').style.display='none'">&times;</span>
                    <h2 class="text-2xl font-bold mb-4">Form Add Data</h2>
                    <?php
                    if (isset($_SESSION['prg_message'])) {
                        echo '<div class="text-green-600 mb-4">' . $_SESSION['prg_message'] . '</div>';
                        unset($_SESSION['prg_message']);
                    }
                    if (isset($_SESSION['prg_error'])) {
                        echo '<div class="text-red-600 mb-4">' . $_SESSION['prg_error'] . '</div>';
                        unset($_SESSION['prg_error']);
                    }
                    ?>
                    <form action="data_berkas_kontingen.php" method="POST" enctype="multipart/form-data" class="space-y-4" onsubmit="showLoader()">
                        <div>
                            <label for="nama_berkas" class="block text-gray-700 text-sm font-bold mb-2">Nama Berkas:</label>
                            <input type="text" id="nama_berkas" name="nama_berkas" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        </div>

                        <div>
                            <label for="file_berkas" class="block text-gray-700 text-sm font-bold mb-2">File:</label>
                            <input type="file" id="file_berkas" name="file_berkas" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        </div>
                        <div>
                            <label for="kontingen" class="block text-gray-700 text-sm font-bold mb-2">Kontingen:</label>
                            <select name="kontingen" id="kontingen" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                <option value="">Pilih Kontingen</option>
                                <option value="utara">Utara</option>
                                <option value="tengah">Tengah</option>
                                <option value="selatan">Selatan</option>
                                <option value="saka">Saka</option>
                            </select>
                        </div>


                        <div>
                            <label for="catatan_dokumen" class="block text-gray-700 text-sm font-bold mb-2">Catatan Dokumen:</label>
                            <input type="text" id="catatan_dokumen" name="catatan_dokumen" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        </div>
                        <input type="hidden" name="status" value="terkirim">
                        <div class="flex justify-end space-x-2">
                            <button type="submit" name="add_berkas" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">Save changes</button>
                            <button type="button" onclick="document.getElementById('addBerkasModal').style.display='none'" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">Close</button>
                        </div>
                    </form>
                </div>
            </div>
  


            
                      <div class="flex flex-wrap gap-1 mb-1">
                        <input type="text" id="searchInput" placeholder="Cari nama berkas..." class="border p-2 rounded mb-4 w-full max-w-sm">
                        <?php if (in_array($role, ['admin', 'cabang'])): ?>
                            <form method="GET" class="mb-4 flex gap-2">
                                <label for="filter_post_by" class="text-sm font-semibold mt-2">Filter Post By:</label>
                                <select name="filter_post_by" id="filter_post_by" class="border rounded p-2">
                                    <option value="">Semua</option>
                                    <option value="utara" <?= $post_by_filter === 'utara' ? 'selected' : '' ?>>Utara</option>
                                    <option value="tengah" <?= $post_by_filter === 'tengah' ? 'selected' : '' ?>>Tengah</option>
                                    <option value="selatan" <?= $post_by_filter === 'selatan' ? 'selected' : '' ?>>Selatan</option>
                                    <option value="saka" <?= $post_by_filter === 'saka' ? 'selected' : '' ?>>Saka</option>
                                </select>
                                <button type="submit" class="bg-blue-500 text-white px-3 rounded hover:bg-blue-600">Terapkan</button>
                            </form>
                            <?php endif; ?>
                            <form method="POST" action="./export/export_csv.php" class="inline">
                                <input type="hidden" name="post_by_filter" value="<?= $post_by_filter ?>">
                                <button type="submit" class="bg-gray-700 text-white px-4 py-2 rounded hover:bg-gray-800 mb-4 gap-4">Export CSV</button>
                            </form>
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
                <table id="berkasTable" class="min-w-full bg-white border border-gray-300">
                    <thead>
                        <tr class="bg-gray-200 text-gray-600 uppercase text-sm leading-normal">
                            <th class="py-3 px-6 text-left border-b border-gray-300">No</th>
                            <th class="py-3 px-6 text-left border-b border-gray-300">Nama</th>
                            <th class="py-3 px-6 text-left border-b border-gray-300">File</th>
                            <th class="py-3 px-6 text-left border-b border-gray-300">Status</th>
                            <th class="py-3 px-6 text-left border-b border-gray-300">Catatan Dokumen</th>
                            <th class="..." onclick="sortTable(5)">Uploaded At ⬍</th>
                            <th class="py-3 px-6 text-left border-b border-gray-300">Post By</th>
                            <th class="py-3 px-6 text-left border-b border-gray-300">Action</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-600 text-sm font-light">
                        <?php
                        if ($result_berkas->num_rows > 0) {
                            $no = 1;
                            while($row = $result_berkas->fetch_assoc()) {
                                echo "<tr class='border-b border-gray-200 hover:bg-gray-100'>";
                                echo "<td class='py-3 px-6 text-left whitespace-nowrap'>" . $no++ . "</td>";
                                echo "<td class='py-3 px-6 text-left'>" . ($row['nama_berkas']) . "</td>";
                                echo "<td class='py-3 px-6 text-left'><a href='../uploads/berkas_kontingen/" . ($row['file_path']) . "' target='_blank' class='text-blue-600 hover:underline'>Lihat File</a></td>";
                                $status = strtolower(trim($row['status']));
                                $statusClass = '';
                                $statusText = '';
                                if ($status == 'terkirim') {
                                    $statusClass = "bg-green-200 text-green-600 py-1 px-3 rounded-full text-xs";
                                    $statusText = "Terkirim";
                                } elseif ($status == 'tertunda') {
                                    $statusClass = "bg-yellow-200 text-yellow-600 py-1 px-3 rounded-full text-xs";
                                    $statusText = "Tertunda";
                                } else {
                                    $statusClass = "bg-gray-200 text-gray-600 py-1 px-3 rounded-full text-xs";
                                    $statusText = "Tidak diketahui";
                                }
                                echo "<td class='py-3 px-6 text-left'><span class='$statusClass'>$statusText</span></td>";
                                echo "<td class='py-3 px-6 text-left'>" . ($row['catatan_dokumen']) . "</td>";
                                echo "<td class='py-3 px-6 text-left'>" . ($row['uploaded_at']) . "</td>";
                                echo "<td class='py-3 px-6 text-left'>" . ($row['post_by']) . "</td>";
                                echo "<td class='py-3 px-6 text-left'>";
                                if ($role !== 'cabang') {
                                    echo "<a href='edit_berkas_kontingen.php?id=" . $row['id'] . "' class='bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-1 px-3 rounded mr-2'>Edit</a>";
                                    echo "<a href='data_berkas_kontingen.php?delete_id=" . $row['id'] . "' class='bg-red-500 hover:bg-red-700 text-white font-bold py-1 px-3 rounded' onclick='return confirm(\"Yakin ingin menghapus berkas ini?\");'>Delete</a>";
                                }
                                echo "</td>";

                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='8' class='py-3 px-6 text-center'>No data available in table</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
                                    <div class="flex  mt-6 space-x-2">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?= $page - 1 ?>" class="px-3 py-2 bg-gray-300 text-gray-800 rounded hover:bg-gray-400">&laquo; Prev</a>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="?page=<?= $i ?>" class="px-3 py-2 <?= $i == $page ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-800' ?> rounded hover:bg-gray-400"><?= $i ?></a>
                    <?php endfor; ?>

                    <?php if ($page < $total_pages): ?>
                        <a href="?page=<?= $page + 1 ?>" class="px-3 py-2 bg-gray-300 text-gray-800 rounded hover:bg-gray-400">Next &raquo;</a>
                    <?php endif; ?>
                    </div>

            </div>
        </section>
    </div>

    <div id="loader" class="hidden fixed inset-0  items-center justify-center bg-black bg-opacity-50 z-50">
  <div class="animate-spin rounded-full h-16 w-16 border-t-4 border-blue-500"></div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>



<script>
function exportToExcel(tableID, filename = 'Data_Berkas_Kontingen.xlsx') {
  const table = document.getElementById(tableID);
  const wb = XLSX.utils.table_to_book(table, { sheet: "Sheet1" });
  XLSX.writeFile(wb, filename);
}
</script>


<script>
async function exportToPDF() {
  const { jsPDF } = window.jspdf;
  const doc = new jsPDF();

  doc.text("Data Berkas Kontingen", 14, 15);

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
  function showLoader() {
    document.getElementById('loader').classList.remove('hidden');
  }
</script>
   
<script>
  <?php if (isset($_SESSION['prg_message'])): ?>
    alert("<?= $_SESSION['prg_message']; ?>");
    <?php unset($_SESSION['prg_message']); ?>
  <?php elseif (isset($_SESSION['prg_error'])): ?>
    alert("<?= $_SESSION['prg_error']; ?>");
    <?php unset($_SESSION['prg_error']); ?>
  <?php endif; ?>
</script>

<!-- Tambahkan fungsi sortTable -->
<script>
function sortTable(col) {
  let table = document.querySelector("table");
  let switching = true;
  let shouldSwitch, i, rows = table.rows;
  let dir = "asc", switchcount = 0;

  while (switching) {
    switching = false;
    for (i = 1; i < (rows.length - 1); i++) {
      shouldSwitch = false;
      let x = rows[i].getElementsByTagName("TD")[col];
      let y = rows[i + 1].getElementsByTagName("TD")[col];

      if ((dir === "asc" && x.innerText > y.innerText) || 
          (dir === "desc" && x.innerText < y.innerText)) {
        shouldSwitch = true;
        break;
      }
    }
    if (shouldSwitch) {
      rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
      switching = true;
      switchcount++;
    } else {
      if (switchcount == 0 && dir == "asc") {
        dir = "desc";
        switching = true;
      }
    }
  }
}
</script>


</body>
</html>