

<?php
session_start();
$name = $_SESSION['name'] ?? null;
?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>


<div class="page-content">
  <nav class="page-breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="#">Pages</a></li>
      <li class="breadcrumb-item" aria-current="page">FG Pulling History</li>
    </ol>
  </nav>

  <div class="row">
    <div class="col-md-12 grid-margin stretch-card">
      <div class="card">
        <div class="card-body">
          <h6 class="card-title">Pulled out History</h6>
<div class="row mb-3">
  <div class="col-md-3">
    <select id="column-select" class="form-select">
      <option value="" disabled selected>Select Column</option>
      <option value="material_no">Material No</option>
      <option value="material_description">Material Description</option>
      <option value="model">Model</option>
      <option value="total_quantity">Total Quantity</option>
      <option value="shift">Shift</option>
      <option value="lot_no">Lot No</option>
      <option value="date_needed">Date Needed</option>
      <option value="pulled_at">Pulled At</option>
    </select>
  </div>
  <div class="col-md-4">
    <input type="text" id="search-input" class="form-control" placeholder="Type to filter..." />
  </div>
</div>

  

<table class="table" style="table-layout: fixed; width: 100%;">
  <thead>
    <tr>
      <th style="width: 10%; text-align: center;">Material No</th>
      <th style="width: 15%; text-align: center;">Material Description</th>
      <th style="width: 5%; text-align: center;">Model</th>
      <th style="width: 7%; text-align: center;">Total Quantity</th>
      <th style="width: 7%; text-align: center;">Shift</th>
      <th style="width: 5%; text-align: center;">Lot No</th>
      <th style="width: 10%; text-align: center;">Date Needed</th>
      <th style="width: 10%; text-align: center;">Pulled at</th>
      
    </tr>
  </thead>
  <tbody id="data-body" style="word-wrap: break-word; white-space: normal;"></tbody>
</table>


      
      </div>
    </div>
  </div>
</div>

<script src="assets/js/sweetalert2@11.js"></script>
<script>
let allData = [];  // store fetched data globally
document.getElementById('search-input').addEventListener('input', () => {
  const column = document.getElementById('column-select').value;
  const query = document.getElementById('search-input').value.toLowerCase();

  if (!column) return; // Do nothing if no column is selected

  const filtered = allData.filter(item => {
    const value = (item[column] ?? '').toString().toLowerCase();
    return value.includes(query);
  });

  renderTable(filtered);
});
function renderTable(data) {
  const tbody = document.getElementById('data-body');
  tbody.innerHTML = '';

  // Sort data by latest pulled_at
  const sortedData = [...data].sort((a, b) => new Date(b.pulled_at) - new Date(a.pulled_at));

  sortedData.forEach(item => {
    const row = document.createElement('tr');

    row.innerHTML = `
      <td style="text-align: center;">${item.material_no || ''}</td>
      <td class="text-center text-truncate" style="max-width: 200px;">${item.material_description || ''}</td>
      <td style="text-align: center;">${item.model || ''}</td>
      <td style="text-align: center;">${item.total_quantity || ''}</td>
      <td style="text-align: center;">${item.shift || ''}</td>
      <td style="text-align: center;">${item.lot_no || ''}</td>
      <td style="text-align: center;">${item.date_needed || ''}</td>
      <td style="text-align: center;">${item.pulled_at}</td>
    `;

    tbody.appendChild(row);
  });
}

function loadTable() {
  fetch('api/warehouse/getPullingHistory.php')
    .then(response => response.json())
    .then(data => {
      console.log(data)
      allData = data;  // cache all data
      renderTable(allData);
    })
    .catch(error => {
      console.error('Error loading data:', error);
    });
}

// Initial load
loadTable();

</script>
