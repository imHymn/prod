<?php
session_start();
$name = $_SESSION['name'] ?? null;
?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<div class="page-content">
  <nav class="page-breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="#">Pages</a></li>
      <li class="breadcrumb-item active" aria-current="page">Material Inventory Section</li>
    </ol>
  </nav>

  <div class="row">
    <div class="col-md-12 grid-margin stretch-card">
      <div class="card">
        <div class="card-body">
          <h6 class="card-title">Material Components</h6>

          <table class="table" style="table-layout: fixed; width: 100%;">
            <thead>
              <tr>
                <th style="width: 10%; text-align: center;">Material No</th>
                <th style="width: 15%; text-align: center;">Material Description</th>
                <th style="width: 7%; text-align: center;">Model</th>
                <th style="width: 7%; text-align: center;">Quantity</th>
              </tr>
            </thead>
            <tbody id="data-body" style="word-wrap: break-word; white-space: normal;"></tbody>
          </table>

        </div>
      </div>
    </div>
  </div>
</div>

<script>
function renderTable(data) {
  const tbody = document.getElementById('data-body');
  tbody.innerHTML = '';

  data.forEach(item => {
    const quantity = parseInt(item.quantity, 10) || 0;
    const row = document.createElement('tr');

    row.innerHTML = `
      <td class="text-center">${item.material_no || ''}</td>
      <td class="text-center text-truncate" style="max-width: 200px;">${item.material_description || ''}</td>
      <td class="text-center">${item.model_name || ''}</td>
      <td class="text-center">${quantity}</td>
    `;

    tbody.appendChild(row);
  });
}

function loadTable() {
  fetch('api/warehouse/getStockWarehouse.php')
    .then(response => response.json())
    .then(data => renderTable(data))
    .catch(error => console.error('Error loading data:', error));
}

loadTable();
</script>
