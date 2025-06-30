
  const rowsPerPage = 10;
  const rows = document.querySelectorAll('#berkasTable tbody tr');
  const totalPages = Math.ceil(rows.length / rowsPerPage);
  let currentPage = 1;

  function showPage(page) {
    rows.forEach((row, index) => {
      row.style.display = (index >= (page - 1) * rowsPerPage && index < page * rowsPerPage) ? '' : 'none';
    });
    currentPage = page;
    document.getElementById('pageIndicator').innerText = `Halaman ${currentPage} dari ${totalPages}`;
  }

  function nextPage() { if (currentPage < totalPages) showPage(currentPage + 1); }
  function prevPage() { if (currentPage > 1) showPage(currentPage - 1); }

  window.onload = () => showPage(1);


<div class="flex justify-between items-center mt-4">
  <button onclick="prevPage()" class="bg-gray-300 px-4 py-2 rounded">Sebelumnya</button>
  <span id="pageIndicator" class="text-sm text-gray-600"></span>
  <button onclick="nextPage()" class="bg-gray-300 px-4 py-2 rounded">Berikutnya</button>
</div>
