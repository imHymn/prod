<script src="assets/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/sweetalert2@11.js"></script>
<script src="assets/js/html5.qrcode.js"></script>
<?php include './components/reusable/tablesorting.php'; ?>
<?php include './components/reusable/tablepagination.php'; ?>
<?php include './components/reusable/searchfilter.php'; ?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

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
                <th style="width: 5%; text-align: center;">Raw Materials <span class="sort-icon"></span></th>
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
<div class="modal fade" id="rawMaterialModal" tabindex="-1" aria-labelledby="rawMaterialModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="rawMaterialModalLabel">Raw Materials</h5>

      </div>
      <div class="modal-body">
        <table class="table table-bordered table-sm">
          <thead>
            <tr>
              <th>Material No</th>
              <th>Description</th>
              <th>Usage</th>
            </tr>
          </thead>
          <tbody id="raw-material-body"></tbody>
        </table>
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
    console.log(data);
    dataBody.innerHTML = '';

    if (!data || data.length === 0) {
      dataBody.innerHTML = `<tr><td colspan="4" style="text-align:center;">No records found</td></tr>`;
      return;
    }

    data.forEach(item => {
      const row = document.createElement('tr');
      row.innerHTML = `
      <td style="text-align: center;">${item.material_no || ''}</td>
      <td style="text-align: center;">${item.component_name || ''}</td>
      <td style="text-align: center;">
        ${item.rm_quantity || 0}
       <button class="btn btn-sm view-btn" 
        data-material="${item.material_no}" 
        data-component="${item.component_name}" 
        data-raw="${encodeURIComponent(JSON.stringify(item.raw_materials || []))}"
        data-quantity="${item.rm_quantity || 0}"
        title="View Details">
  <i class="fas fa-eye" style="font-size:16px;margin-left:-15px;margin-top:-2px;"></i>
</button>


      </td>
      <td style="text-align: center;">${item.delivered_at || ''}</td>
    `;
      dataBody.appendChild(row);
    });

    const now = new Date();
    document.getElementById('last-updated').textContent = `Last updated: ${now.toLocaleString()}`;
  }
  document.addEventListener('click', function(e) {
    const btn = e.target.closest('.view-btn');
    if (!btn) return;

    const rawEncoded = btn.dataset.raw;
    const baseQty = parseInt(btn.dataset.quantity) || 0; // rm_quantity

    let rawMaterials;

    try {
      const rawDecoded = decodeURIComponent(rawEncoded);
      rawMaterials = typeof rawDecoded === 'string' ? JSON.parse(rawDecoded) : rawDecoded;

      // Handle double-encoded JSON
      if (typeof rawMaterials === 'string') {
        rawMaterials = JSON.parse(rawMaterials);
      }
    } catch (err) {
      console.error("Invalid raw materials:", err);
      return;
    }

    const tbody = document.getElementById('raw-material-body');
    tbody.innerHTML = '';

    if (Array.isArray(rawMaterials) && rawMaterials.length > 0) {
      rawMaterials.forEach(mat => {
        const totalUsage = (parseFloat(mat.usage) || 0) * baseQty;
        const tr = document.createElement('tr');
        tr.innerHTML = `
        <td>${mat.material_no}</td>
        <td>${mat.material_description}</td>
        <td>${totalUsage}</td>
      `;
        tbody.appendChild(tr);
      });
    } else {
      tbody.innerHTML = '<tr><td colspan="3" class="text-center">No raw materials found</td></tr>';
    }

    const modal = new bootstrap.Modal(document.getElementById('rawMaterialModal'));
    modal.show();
  });



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