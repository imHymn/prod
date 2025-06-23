<?php include './components/reusable/tablepagination.php'; ?>
<?php include './components/reusable/tablesorting.php'; ?>
<?php include './components/reusable/searchfilter.php'; ?>
<script src="assets/js/bootstrap.bundle.min.js"></script>

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

          <div class="d-flex align-items-center justify-content-between mb-2">
            <h6 class="card-title">Material Components</h6>
            <small id="last-updated" class="text-muted" style="font-size:13px;"></small>
          </div>
          <div class="row mb-3">
            <div class="col-md-3">
              <select id="filter-column" class="form-select">
                <option value="" disabled selected>Select Column to Filter</option>
                <option value="material_no">Material No</option>
                <option value="material_description">Material Description</option>
                <option value="model_name">Model</option>
              </select>
            </div>
            <div class="col-md-4">
              <input
                type="text"
                id="filter-input"
                class="form-control"
                placeholder="Type to filter..."
                disabled />
            </div>
          </div>
          <table class="table" style="table-layout: fixed; width: 100%;">
            <thead>
              <tr>
                <th style="width: 10%; text-align: center;">Material No <span class="sort-icon"></span></th>
                <th style="width: 15%; text-align: center;">Material Description <span class="sort-icon"></span></th>
                <th style="width: 7%; text-align: center;">Model <span class="sort-icon"></span></th>
                <th style="width: 7%; text-align: center;">Quantity <span class="sort-icon"></span></th>
              </tr>
            </thead>
            <tbody id="data-body" style="word-wrap: break-word; white-space: normal;"></tbody>
          </table>
          <div id="pagination" class="mt-3 d-flex justify-content-center"></div>


        </div>
      </div>
    </div>
  </div>
</div>

<script>
  let fullData = [];
  let paginator;

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

    const now = new Date();
    document.getElementById('last-updated').textContent = `Last updated: ${now.toLocaleString()}`;
  }

  function loadTable() {
    fetch('api/warehouse/getStockWarehouse.php')
      .then(response => response.json())
      .then(data => {
        fullData = data.filter(item => item.model_name === 'L300');

        paginator = createPaginator({
          data: fullData,
          rowsPerPage: 10,
          paginationContainerId: 'pagination',
          renderPageCallback: renderTable
        });

        paginator.render();
        setupSearchFilter({
          filterColumnSelector: '#filter-column',
          filterInputSelector: '#filter-input',
          data: fullData,
          onFilter: (filtered) => {
            paginator.setData(filtered);
          }
        });

      })
      .catch(error => console.error('Error loading data:', error));
  }

  loadTable();


  enableTableSorting(".table");
</script>