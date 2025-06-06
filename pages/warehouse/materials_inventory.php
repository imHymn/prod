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
  <div class="row mb-3">
    <div class="col-md-3">
      <select id="filter-column" class="form-select">
        <option value="" disabled selected>Select Column to Filter</option>
        <option value="model_name">Model</option>
        <option value="material_no">Material No</option>
        <option value="lot_no">Lot No</option>
        <option value="shift">Shift</option>
        <option value="quantity">Quantity</option>
        <option value="status">Status</option>
        <option value="person_incharge">Person Incharge</option>
      </select>
    </div>
    <div class="col-md-4">
      <input
        type="text"
        id="filter-input"
        class="form-control"
        placeholder="Type to filter..."
        disabled
      />
    </div>
  </div>

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
let fullData = [];

function renderTable(data) {
  const tbody = document.getElementById('data-body');
  tbody.innerHTML = '';

  data.forEach(item => {
    if (item.model_name !== 'L300') { return; }
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
    .then(data => {
      fullData = data;
      renderTable(fullData);
    })
    .catch(error => console.error('Error loading data:', error));
}

loadTable();

const filterColumn = document.getElementById('filter-column');
const filterInput = document.getElementById('filter-input');

filterColumn.addEventListener('change', () => {
  if (filterColumn.value) {
    filterInput.disabled = false;
    filterInput.value = '';
    renderTable(fullData); // reset filter
  } else {
    filterInput.disabled = true;
    filterInput.value = '';
    renderTable(fullData);
  }
});

filterInput.addEventListener('input', () => {
  const searchTerm = filterInput.value.trim().toLowerCase();
  const column = filterColumn.value;

  if (!column || !searchTerm) {
    renderTable(fullData);
    return;
  }

  const filteredData = fullData.filter(item => {
    if (item.model_name !== 'L300') return false;

    let fieldValue = item[column];

    if (fieldValue === undefined || fieldValue === null) return false;

    // For quantity, convert to string (in case it's a number)
    if (typeof fieldValue !== 'string') {
      fieldValue = String(fieldValue);
    }

    return fieldValue.toLowerCase().includes(searchTerm);
  });

  renderTable(filteredData);
});
</script>
