
<?php
session_start();
include('../includes/db.php');
include('../includes/auth.php');
include('../includes/functions.php');

$role = $_SESSION['role'];
$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

$filter_kontingen = '';
if (($role === 'admin' || $role === 'cabang') && isset($_GET['kontingen'])) {
    $filter_kontingen = $conn->real_escape_string($_GET['kontingen']);
}

$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$where = [];
if (!in_array($role, ['admin', 'cabang'])) {
    $where[] = "user_id = '$user_id'";
}
if (!empty($filter_kontingen)) {
    $where[] = "kontingen = '$filter_kontingen'";
}
if (!empty($search)) {
    $where[] = "(nama_berkas LIKE '%$search%' OR kontingen LIKE '%$search%')";
}
$where_sql = count($where) ? "WHERE " . implode(" AND ", $where) : "";

$total_result = $conn->query("SELECT COUNT(*) as total FROM berkas_kontingen $where_sql");
$total_rows = $total_result->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $limit);

$sql = "SELECT bk.*, u.username FROM berkas_kontingen bk 
        JOIN users u ON u.id = bk.user_id
        $where_sql
        ORDER BY bk.uploaded_at DESC 
        LIMIT $limit OFFSET $offset";
$result = $conn->query($sql);

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_berkas'])) {
    $nama_berkas = $conn->real_escape_string($_POST['nama_berkas']);
    $catatan_dokumen = $conn->real_escape_string($_POST['catatan_dokumen'] ?? '');
    $status = 'terkirim';
    $uploaded_at = date('Y-m-d H:i:s');
    $post_by = $_SESSION['role'];
    $kontingen = $_SESSION['role']; // atau ambil dari $_SESSION['kontingen'] jika tersedia

    $target_dir = "../uploads/berkas_kontingen/";
    if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);

    $file_name = uniqid() . "_" . basename($_FILES["file_berkas"]["name"]);
    $target_file = $target_dir . $file_name;
    $fileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    $allowed = ['pdf', 'docx', 'jpg', 'jpeg', 'png'];
    if (in_array($fileType, $allowed)) {
        if (move_uploaded_file($_FILES["file_berkas"]["tmp_name"], $target_file)) {
            $sql = "INSERT INTO berkas_kontingen (user_id, nama_berkas, file_path, status, catatan_dokumen, post_by, kontingen, uploaded_at)
                    VALUES ('$user_id', '$nama_berkas', '$file_name', '$status', '$catatan_dokumen', '$post_by', '$kontingen', '$uploaded_at')";
            if ($conn->query($sql)) {
                $_SESSION['prg_message'] = "Berkas berhasil ditambahkan.";
            } else {
                $_SESSION['prg_error'] = "Gagal simpan ke DB: " . $conn->error;
            }
        } else {
            $_SESSION['prg_error'] = "Gagal mengunggah file.";
        }
    } else {
        $_SESSION['prg_error'] = "Format file tidak valid.";
    }

    header("Location: data_berkas_kontingen.php");
    exit;
}


include('../templates/header.php');
include('../templates/sidebar.php');
?>

<div class="main-content flex-1 p-10 md:ml-64">
  <header class="bg-white shadow p-6 rounded-lg mb-6 flex justify-between items-center">
    <h1 class="text-2xl font-bold text-gray-800">Data Berkas Kontingen</h1>

  </header>

  <section class="bg-white p-6 rounded-lg shadow">

      <?php if (in_array($role, ['admin', 'utara', 'selatan', 'tengah', 'saka'])): ?>
   <button onclick="document.getElementById('modalAdd').classList.remove('hidden')" class="bg-blue-600 text-white px-4 py-2 rounded shadow hover:bg-blue-700 mb-3">
    + Tambah Berkas
</button>
    <?php endif; ?>
<?php if ($role === 'admin' || $role === 'cabang'): ?>
  <form method="GET" class="mb-4 flex flex-wrap gap-2">
    <label for="kontingen" class="mt-2">Filter Kontingen:</label>
    <select name="kontingen" id="kontingen" onchange="this.form.submit()" class="border p-2 rounded">
        <option value="all ">Semua</option>
        <option value="selatan" <?= $kontingen === 'selatan' ? 'selected' : '' ?>>Selatan</option>
        <option value="tengah" <?= $kontingen === 'tengah' ? 'selected' : '' ?>>Tengah</option>
        <option value="utara" <?= $kontingen === 'utara' ? 'selected' : '' ?>>Utara</option>
        <option value="saka" <?= $kontingen === 'saka' ? 'selected' : '' ?>>Saka</option>
        <option value="cabang" <?= $kontingen === 'cabang' ? 'selected' : '' ?>>Cabang</option>
    </select>
  </form>
<?php endif; ?>

      <input type="text" name="search" placeholder="Cari berkas..." value="<?= htmlspecialchars($search) ?>" class="border p-2 rounded">
      <button type="submit" class="bg-gray-600 text-white px-4 py-2 rounded">Cari</button>
    </form>

    <div class="flex gap-2 mb-4">
      <a href="./export/export_csv.php?<?= http_build_query($_GET) ?>" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Export CSV</a>
      <a href="./export/export_excel.php?<?= http_build_query($_GET) ?>" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Export Excel</a>
      <a href="./export/export_pdf.php?<?= http_build_query($_GET) ?>" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">Export PDF</a>
    </div>

    <div class="overflow-x-auto">
      <table class="min-w-full table-auto border border-gray-300">
        <thead>
          <tr class="bg-gray-100 text-gray-700 text-sm">
            <th class="p-2 border">No</th>
            <th class="p-2 border">Nama Berkas</th>
            <th class="p-2 border">Kontingen</th>
            <th class="p-2 border">Diupload Oleh</th>
            <th class="p-2 border">Status</th>
            <th class="p-2 border">Aksi</th>
          </tr>
        </thead>
        <tbody>
        <?php if ($result->num_rows): $no = $offset + 1; while ($row = $result->fetch_assoc()): ?>
          <tr class="text-sm border-t hover:bg-gray-50">
            <td class="p-2 border"><?= $no++ ?></td>
            <td class="p-2 border"><?= htmlspecialchars($row['nama_berkas']) ?></td>
            <td class="p-2 border"><?= htmlspecialchars($row['kontingen']) ?></td>
            <td class="p-2 border"><?= htmlspecialchars($row['username']) ?></td>
            <td class="p-2 border"><?= htmlspecialchars($row['status']) ?></td>
            <td class="p-2 border">
              <a href="../uploads/berkas_kontingen/<?= $row['file_path'] ?>" target="_blank" class="text-blue-500 hover:underline">Lihat</a>
              <?php if ($role === 'admin' || $row['user_id'] == $user_id): ?>
                | <a href="edit_berkas.php?id=<?= $row['id'] ?>" class="text-yellow-500 hover:underline">Edit</a>
                | <a href="hapus_berkas.php?id=<?= $row['id'] ?>" onclick="return confirm('Yakin?')" class="text-red-500 hover:underline">Hapus</a>
              <?php endif; ?>
            </td>
          </tr>
        <?php endwhile; else: ?>
          <tr><td colspan="6" class="text-center p-4">Tidak ada data</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div class="mt-4 flex justify-between items-center">
      <span>Halaman <?= $page ?> dari <?= $total_pages ?></span>
      <div class="space-x-1">
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
          <a href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>" class="px-3 py-1 border rounded <?= $i == $page ? 'bg-blue-600 text-white' : 'bg-white text-blue-600' ?>"><?= $i ?></a>
        <?php endfor; ?>
      </div>
    </div>
  </section>
  <?php include('../templates/footer.php'); ?>

</div>

<div id="modalAdd" class="hidden fixed inset-0 bg-black bg-opacity-50 items-center justify-center z-50">
  <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-lg relative">
    <button onclick="document.getElementById('modalAdd').classList.add('hidden')" class="absolute top-2 right-3 text-gray-600 hover:text-black text-2xl font-bold">&times;</button>

    <h2 class="text-2xl font-bold mb-4 text-gray-800">Tambah Berkas Kontingen</h2>

    <form action="data_berkas_kontingen.php" method="POST" enctype="multipart/form-data" class="space-y-4">
      <div>
        <label for="nama_berkas" class="block text-sm font-semibold text-gray-700">Nama Berkas</label>
        <input type="text" name="nama_berkas" id="nama_berkas" required
               class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
      </div>

      <div>
        <label for="file_berkas" class="block text-sm font-semibold text-gray-700">Upload File (PDF, JPG, PNG, DOCX)</label>
        <input type="file" name="file_berkas" id="file_berkas" accept=".pdf,.jpg,.jpeg,.png,.docx" required
               class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
      </div>

      <div>
        <label for="catatan_dokumen" class="block text-sm font-semibold text-gray-700">Catatan</label>
        <textarea name="catatan_dokumen" id="catatan_dokumen"
                  class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
      </div>

      <input type="hidden" name="status" value="terkirim">
      <input type="hidden" name="add_berkas" value="1"> <!-- trigger di backend -->

      <div class="flex justify-end gap-2 pt-3">
        <button type="submit"
                class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded shadow">Simpan</button>
        <button type="button" onclick="document.getElementById('modalAdd').classList.add('hidden')"
                class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded shadow">Batal</button>
      </div>
    </form>
  </div>
</div>
