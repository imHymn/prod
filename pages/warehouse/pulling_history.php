

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

function renderTable(data) {
  const tbody = document.getElementById('data-body');
  tbody.innerHTML = '';

  data.forEach(item => {
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
