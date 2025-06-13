<?php include './components/reusable/tablesorting.php'; ?>
<?php include './components/reusable/tablepagination.php'; ?>

<script src="assets/js/html5.qrcode.js"></script>

<script src="assets/js/sweetalert2@11.js"></script>
<script src="assets/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/jquery.min.js"></script>
<link rel="stylesheet" href="assets/css/choices.min.css" />
<script src="assets/js/choices.min.js"></script>
<script>

</script>

<div class="page-content">
  <nav class="page-breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="#">Pages</a></li>
      <li class="breadcrumb-item" aria-current="page">Delivery Section</li>
    </ol>
  </nav>

  <div class="row">
    <div class="col-md-12 grid-margin stretch-card">
      <div class="card">
        <div class="card-body">
      <div class="d-flex align-items-center justify-content-between mb-2">
  <h6 class="card-title mb-0">List of Pulled Out</h6>
  <small id="last-updated" class="text-muted" style="font-size:13px;"></small>
</div>

<div class="row mb-3">
  <div class="col-md-3">
    <select id="column-select" class="form-select">
      <option value="" disabled selected>Select Column</option>

      <option value="material_no">Material No</option>
      <option value="model_name">Model</option>
      <option value="quantity">Qty</option>
      <option value="supplement_order">Supplement</option>
      <option value="total_quantity">Total Qty</option>
      <option value="shift">Shift</option>
      <option value="lot_no">Lot</option>
      <option value="date_needed">Date Needed</option>
    </select>
  </div>
  <div class="col-md-4">
    <input type="text" id="search-input" class="form-control" placeholder="Type to filter..." />
  </div>
</div>



<table class="table table" style="table-layout: fixed; width: 100%;">
  <thead>
    <tr>
      <th style="width: 10%; text-align: center;">Material No <span class="sort-icon"></span></th>
      <!-- <th style="width: 20%; text-align: center;">Description</th> -->
      <th style="width: 10%; text-align: center;">Model <span class="sort-icon"></span></th>
      <th style="width: 8%; text-align: center;">Qty <span class="sort-icon"></span></th>
      <th style="width: 8%; text-align: center;">Supplement <span class="sort-icon"></span></th>
      <th style="width: 8%; text-align: center;">Total Qty <span class="sort-icon"></span></th>
      <th style="width: 8%; text-align: center;">Shift <span class="sort-icon"></span></th>
      <th style="width: 8%; text-align: center;">Lot <span class="sort-icon"></span></th>
      <th style="width: 25%; text-align: center;">Date Needed <span class="sort-icon"></span></th>
    </tr>
  </thead>
  <tbody id="data-body"></tbody>
</table>

<div id="pagination" class="mt-3 d-flex justify-content-center"></div>
      </div>
    </div>
  </div>
</div>


<script>
  let paginator;
let originalData = [];
let filteredData = [];

document.addEventListener('DOMContentLoaded', () => {
  fetch('api/delivery/getPulled_out.php')
    .then(res => res.json())
    .then(data => {
      const now = new Date();
      document.getElementById('last-updated').textContent = `Last updated: ${now.toLocaleString()}`;

      // Store original data and initialize the paginator
      originalData = data;
      filteredData = data;  // Initially, filtered data is the same as original data

      paginator = createPaginator({
        data: filteredData, // Use filteredData for pagination
        rowsPerPage: 10,
        paginationContainerId: 'pagination',
        renderPageCallback: renderPulledOutTable,
        defaultSortFn: (a, b) => new Date(b.date_needed) - new Date(a.date_needed)
      });

      paginator.render();
    })
    .catch(console.error);
});

function renderPulledOutTable(pageData) {
  const tbody = document.getElementById('data-body');
  tbody.innerHTML = ''; // Clear the existing rows

  pageData.forEach(item => {
    const row = document.createElement('tr');
    row.innerHTML = `
      <td style="text-align: center;">${item.material_no}</td>
      <td style="text-align: center;">${item.model_name}</td>
      <td style="text-align: center;">${item.quantity}</td>
      <td style="text-align: center;">${item.supplement_order ?? '0'}</td>
      <td style="text-align: center;">${item.total_quantity}</td>
      <td style="text-align: center;">${item.shift}</td>
      <td style="text-align: center;">${item.lot_no}</td>
      <td style="text-align: center;">${item.date_needed || '<i>NONE</i>'}</td>
    `;
    tbody.appendChild(row);
  });
}

function dynamicSearch() {
  const column = document.getElementById('column-select').value;
  const searchText = document.getElementById('search-input').value.trim().toLowerCase();

  // Filter the data based on the selected column and search text
  filteredData = originalData.filter(item =>
    (item[column] ?? '').toString().toLowerCase().includes(searchText)
  );

  // Update the paginator with the filtered data
  paginator.setData(filteredData);
}

const columnSelect = document.getElementById('column-select');
const searchInput = document.getElementById('search-input');

// Disable search input by default
searchInput.disabled = true;

columnSelect.addEventListener('change', () => {
  searchInput.disabled = !columnSelect.value;
  dynamicSearch();  // Apply the filter immediately when a column is selected
});

searchInput.addEventListener('input', dynamicSearch);  // Apply filter when user types in search

  enableTableSorting(".table");
</script>
