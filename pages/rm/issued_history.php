<script src="assets/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/sweetalert2@11.js"></script>
<script src="assets/js/html5.qrcode.js"></script>
<?php include './components/reusable/tablesorting.php'; ?>
<?php include './components/reusable/tablepagination.php'; ?>
<?php include './components/reusable/searchfilter.php'; ?>
<link rel="stylesheet" href="assets/css/all.min.css" />
<script src="assets/js/bootstrap.bundle.min.js"></script>

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
                <option value="component_name">Material Description</option>
                <option value="quantity">Quantity</option>
                <option value="created_at">Time & Date</option>
              </select>
            </div>
            <div class="col-md-4">
              <input type="text" id="filter-input" class="form-control" placeholder="Type to filter..." disabled />
            </div>
          </div>
          <table class="table" style="table-layout: fixed; width: 100%;">
            <thead>
              <tr>
                <th style="width: 5%; text-align: center;">Material No <span class="sort-icon"></span></th>
                <th style="width: 10%; text-align: center;">Material Description <span class="sort-icon"></span></th>
                <th style="width: 20%; text-align: center;">Raw Materials <span class="sort-icon"></span></th>
                <th style="width: 5%; text-align: center;">Quantity <span class="sort-icon"></span></th>
                <th style="width: 10%; text-align: center;">Time & Date <span class="sort-icon"></span></th>
              </tr>
            </thead>
            <tbody id="data-body"></tbody>
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

  const dataBody = document.getElementById('data-body');
  const filterColumn = document.getElementById('filter-column');
  const filterInput = document.getElementById('filter-input');

  function renderTable(data) {
    dataBody.innerHTML = '';

    if (!data || data.length === 0) {
      dataBody.innerHTML = `<tr><td colspan="4" style="text-align:center;">No records found</td></tr>`;
      return;
    }

    data.forEach(item => {
      const rawMaterials = (() => {
        try {
          return typeof item.raw_materials === 'string' ?
            JSON.parse(item.raw_materials) :
            item.raw_materials || [];
        } catch {
          return [];
        }
      })();

      const quantity = parseInt(item.rm_quantity) || 0;

      const rawHTML = rawMaterials.length ? `
      <div style="overflow-x:auto;">
        <table class="table table-sm table-bordered mb-0" style="margin:0; table-layout: fixed; width: 100%;">
          <thead>
            <tr>
              <th style="font-size:10px; padding: 2px; width: 20%;">No</th>
              <th style="font-size:10px; padding: 2px; width: 50%;">Desc</th>
              <th style="font-size:10px; padding: 2px; width: 20%;">Total</th>
            </tr>
          </thead>
          <tbody>
            ${rawMaterials.map(rm => `
              <tr>
                <td style="font-size:10px; padding: 2px;">${rm.material_no}</td>
                <td style="font-size:10px; padding: 2px;">${rm.material_description}</td>
                <td style="font-size:10px; padding: 2px;">${(parseFloat(rm.usage) * quantity).toFixed(2)}</td>
              </tr>
            `).join('')}
          </tbody>
        </table>
      </div>` : '<em style="font-size:12px;">None</em>';

      const row = document.createElement('tr');
      row.innerHTML = `
      <td style="text-align: center;">${item.material_no || ''}</td>
      <td style="text-align: center;">
        ${item.component_name || ''}

      </td>
            <td style="text-align: center;">${rawHTML}</td>
      <td style="text-align: center;">${quantity}</td>
      <td style="text-align: center;">${item.delivered_at || ''}</td>
    `;

      dataBody.appendChild(row);
    });

    const now = new Date();
    document.getElementById('last-updated').textContent = `Last updated: ${now.toLocaleString()}`;
  }


  fetch('api/rm/getIssuedHistory.php')
    .then(response => response.json())
    .then(data => {
      fullData = data.data || [];

      paginator = createPaginator({
        data: fullData,
        rowsPerPage: 10,
        paginationContainerId: 'pagination',
        renderPageCallback: renderTable
      });

      paginator.render();

      // ✅ Setup reusable filter
      setupSearchFilter({
        filterColumnSelector: '#filter-column',
        filterInputSelector: '#filter-input',
        data: fullData,
        onFilter: filtered => paginator.setData(filtered),
        customValueResolver: (item, column) => {
          switch (column) {
            case 'material_no':
              return item.material_no ?? '';
            case 'component_name':
              return item.component_name ?? '';
            case 'quantity':
              return (item.quantity ?? '').toString();
            case 'created_at':
              return item.created_at ?? '';
            default:
              return item[column] ?? '';
          }
        }
      });

      // ✅ Enable input when column is selected
      filterColumn.addEventListener('change', () => {
        filterInput.disabled = !filterColumn.value;
      });
    })
    .catch(error => {
      console.error('Error fetching data:', error);
    });

  enableTableSorting(".table");
</script>