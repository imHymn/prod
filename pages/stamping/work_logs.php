<script src="assets/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/sweetalert2@11.js"></script>
<script src="assets/js/html5.qrcode.js"></script>
<?php include './components/reusable/tablesorting.php'; ?>
<?php include './components/reusable/tablepagination.php'; ?>


<div class="page-content">
  <nav class="page-breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="#">Pages</a></li>
      <li class="breadcrumb-item" aria-current="page">Stamping Manpower Section</li>
    </ol>
  </nav>

  <div class="row">
    <div class="col-md-12 grid-margin stretch-card">
      <div class="card">
        <div class="card-body">

            <div class="d-flex align-items-center justify-content-between mb-2">
  <h6 class="card-title mb-0">To-do List</h6>

  <small id="last-updated" class="text-muted" style="font-size:13px;"></small>
</div>



  <div class="row mb-3">
    <div class="col-md-3">
      <select id="filter-column" class="form-select">
        <option value="" disabled selected>Select Column to Filter</option>
        <option value="material_no">Material No</option>
        <option value="components_name">Material Description</option>
        <option value="total_quantity">Total Quantity</option>
        <option value="quantity">Quantity</option>
        <option value="person_incharge">Person Incharge</option>
        <option value="time_in">Time In</option>
        <option value="time_out">Time Out</option>
      </select>
    </div>
    <div class="col-md-4">
      <input type="text" id="filter-input" class="form-control" placeholder="Type to filter..." disabled />
    </div>
  </div>
<table class="table table" style="table-layout: fixed; width: 100%;">
  <thead>
    <tr>
      <th style="width: 5%; text-align: center;">Material No <span class="sort-icon"></span></th>
      <th style="width: 10%; text-align: center;">Material Description <span class="sort-icon"></span></th>
      <th style="width: 5%; text-align: center;">Process <span class="sort-icon"></span></th>
      <th style="width: 5%; text-align: center;">Total Quantity <span class="sort-icon"></span></th>
      <th style="width: 5%; text-align: center;">Quantity <span class="sort-icon"></span></th>
      <th style="width: 10%; text-align: center;">Person Incharge <span class="sort-icon"></span></th>
      <th style="width: 7%; text-align: center;">Time In <span class="sort-icon"></span></th>
      <th style="width: 7%; text-align: center;">Time Out <span class="sort-icon"></span></th>
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
let fullData = [];
let paginator;

const dataBody = document.getElementById('data-body');
const filterColumn = document.getElementById('filter-column');
const filterInput = document.getElementById('filter-input');

fetch('api/controllers/stamping/getWorklogs.php')
  .then(response => response.json())
  .then(data => {
    fullData = data;

    // Group and sort
    const grouped = {};
    data.forEach(item => {
      if (!grouped[item.reference_no]) grouped[item.reference_no] = [];
      grouped[item.reference_no].push(item);
    });

    const sorted = Object.values(grouped)
      .flatMap(group => group.sort((a, b) => (parseInt(a.stage || 0) - parseInt(b.stage || 0))));

    paginator = createPaginator({
      data: sorted,
      rowsPerPage: 10,
      paginationContainerId: 'pagination',
      renderPageCallback: renderTable
    });

    paginator.render();
  });

function renderTable(data, page = 1) {
  dataBody.innerHTML = '';

  data.forEach(item => {
    const row = document.createElement('tr');
    row.innerHTML = `
      <td style="text-align: center;">${item.material_no || ''}</td>
      <td style="text-align: center;">${item.components_name || '<i>Null</i>'}</td>
      <td style="text-align: center;">${item.stage_name || '<i>Null</i>'}</td>
      <td style="text-align: center;">${item.total_quantity || '<i>Null</i>'}</td>
      <td style="text-align: center;">${item.quantity || '<i>Null</i>'}</td>
      <td style="text-align: center;">${item.person_incharge || '<i>Null</i>'}</td>
      <td style="text-align: center;">${item.time_in || '<i>Null</i>'}</td>
      <td style="text-align: center;">${item.time_out || '<i>Null</i>'}</td>
    `;
    dataBody.appendChild(row);
  });

  const now = new Date();
  document.getElementById('last-updated').textContent = `Last updated: ${now.toLocaleString()}`;
}

filterColumn.addEventListener('change', () => {
  filterInput.value = '';
  filterInput.disabled = !filterColumn.value;
  paginator.setData(fullData);
});

filterInput.addEventListener('input', () => {
  const column = filterColumn.value;
  const keyword = filterInput.value.toLowerCase().trim();

  if (!column || keyword === '') {
    paginator.setData(fullData);
    return;
  }

  const filtered = fullData.filter(item => {
    const field = item[column];
    return field?.toString().toLowerCase().includes(keyword);
  });

  paginator.setData(filtered);
});

enableTableSorting(".table");

</script>