document.getElementById('searchInput').addEventListener('keyup', function () {
  let value = this.value.toLowerCase();
  document.querySelectorAll('tbody tr').forEach(row => {
    row.style.display = row.innerText.toLowerCase().includes(value) ? '' : 'none';
  });
});

// modal open/close logic
