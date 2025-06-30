function exportToExcel(id, filename) { /* XLSX */ }
function exportToPDF(id, filename) { /* jsPDF */ }
function exportToCSV(id, filename) { /* generate CSV */ }


function exportToCSV() {
  const table = document.getElementById("berkasTable");
  const rows = table.querySelectorAll("tbody tr");
  let csv = [];
  rows.forEach(row => {
    if (row.style.display !== 'none') {
      const cols = row.querySelectorAll("td");
      const rowData = [...cols].map(td => `"${td.innerText.replace(/"/g, '""')}"`);
      csv.push(rowData.join(","));
    }
  });
  const csvContent = "data:text/csv;charset=utf-8," + csv.join("\n");
  const link = document.createElement("a");
  link.setAttribute("href", encodeURI(csvContent));
  link.setAttribute("download", "data_berkas_kontingen.csv");
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
}
