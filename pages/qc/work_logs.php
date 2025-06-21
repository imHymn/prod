<?php include './components/reusable/tablesorting.php'; ?>
<?php include './components/reusable/tablepagination.php'; ?>
<?php include './components/reusable/searchfilter.php'; ?>
<div class="page-content">
  <nav class="page-breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="#">Pages</a></li>
      <li class="breadcrumb-item" aria-current="page">Assembly Work Management</li>
    </ol>
  </nav>

  <div class="row">
    <div class="col-md-12 grid-margin stretch-card">
      <div class="card">
        <div class="card-body">
          <div class="d-flex align-items-center justify-content-between mb-2">
            <h6 class="card-title">Work Logs</h6>
            <small id="last-updated" class="text-muted" style="font-size:13px;"></small>
          </div>

          <div class="row mb-3">
            <div class="col-md-3">
              <select id="filter-column" class="form-select">
                <option value="" disabled selected>Select Column</option>
                <option value="person_incharge">Person Incharge</option>
                <option value="material_description">Material Description</option>
                <option value="lot">Lot</option>
                <option value="good">Good</option>
                <option value="no_good">No Good</option>
                <option value="quantity">Quantity</option>
                <option value="date_needed">Date Needed</option>
              </select>

            </div>
            <div class="col-md-4">
              <input type="text" id="filter-input" class="form-control" placeholder="Type to filter..." disabled />
            </div>
          </div>

          <table class="table" style="table-layout: fixed; width: 100%;">
            <thead>
              <tr>
                <th style="text-align: center;width:7%">Person Incharge <span class="sort-icon"></span></th>
                <th style="text-align: center;width:12%">Material Description <span class="sort-icon"></span></th>
                <th style="text-align: center;width:7%">Lot <span class="sort-icon"></span></th>
                <th style="text-align: center;width:7%">Good <span class="sort-icon"></span></th>
                <th style="text-align: center;width:7%">No Good <span class="sort-icon"></span></th>
                <th style="text-align: center;width:7%">Quantity <span class="sort-icon"></span></th>
                <th style="text-align: center;width:10%">Time In <span class="sort-icon"></span></th>
                <th style="text-align: center;width:10%">Time Out <span class="sort-icon"></span></th>
                <th style="text-align: center;width:7%">Date Needed <span class="sort-icon"></span></th>
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
  const tbody = document.getElementById('data-body');
  const filterColumn = document.getElementById('filter-column');
  const filterInput = document.getElementById('filter-input');

  let paginator = null;
  let allData = [];

  function renderTable(data) {
    tbody.innerHTML = '';
    data.forEach(item => {
      const row = document.createElement('tr');
      row.innerHTML = `
        <td style="text-align: center;white-space: normal; word-wrap: break-word;">${item.person_incharge || ''}</td>
        <td style="text-align: center; white-space: normal; word-wrap: break-word;">${item.material_description || ''}</td>
        <td style="text-align: center;">${item.lot_model || ''}</td>
        <td style="text-align: center;">${item.good}</td>
        <td style="text-align: center;">${item.no_good}</td>
        <td style="text-align: center;">${item.quantity}</td>
        <td style="text-align: center;">${item.time_in || ''}</td>
        <td style="text-align: center;">${item.time_out || ''}</td>
        <td style="text-align: center;">${item.date_needed || ''}</td>
      `;
      tbody.appendChild(row);
    });

    document.getElementById('last-updated').textContent = `Last updated: ${new Date().toLocaleString()}`;
  }

  function loadData() {
    tbody.innerHTML = '';
    allData = [];

    fetch('api/qc/getQCData.php')
      .then(res => res.json())
      .then(qcData => {
        qcData.forEach(item => {
          if (item.time_out === null) return;
          allData.push({
            material_no: item.material_no,
            material_description: item.material_description,
            lot_model: `${item.model || ''}-${item.lot_no || ''} `,
            quantity: `${item.total_quantity || 0}`,
            good: item.good || 0,
            no_good: item.no_good || 0,
            time_in: item.time_in,
            time_out: item.time_out,
            person_incharge: item.person_incharge,
            date_needed: item.date_needed
          });
        });

        return fetch('api/qc/getReworkData.php');
      })
      .then(res => res.json())
      .then(reworkData => {
        reworkData.forEach(item => {
          if (item.qc_timeout === null) return;
          allData.push({
            material_no: (item.material_no || '') + ' (REWORK)',
            material_description: item.material_description,
            lot_model: `${item.model || ''}-${item.lot_no || ''}`,
            quantity: `${item.quantity || 0}`,
            good: item.good || 0,
            no_good: item.no_good || 0,
            time_in: item.qc_timein,
            time_out: item.qc_timeout,
            person_incharge: item.qc_person_incharge,
            date_needed: item.date_needed
          });
        });

        paginator = createPaginator({
          data: allData,
          rowsPerPage: 10,
          paginationContainerId: 'pagination',
          renderPageCallback: renderTable
        });

        paginator.render();

        // ✅ Integrate reusable filter logic
        setupSearchFilter({
          filterColumnSelector: '#filter-column',
          filterInputSelector: '#filter-input',
          data: allData,
          onFilter: filtered => {
            paginator.setData(filtered);
          },
          customValueResolver: (item, column) => {
            switch (column) {
              case 'person_incharge':
                return item.person_incharge ?? '';
              case 'material_description':
                return item.material_description ?? '';
              case 'lot':
                return item.lot_model ?? '';
              case 'good':
                return item.good?.toString() ?? '';
              case 'no_good':
                return item.no_good?.toString() ?? '';
              case 'quantity':
                return item.quantity?.toString() ?? '';
              case 'date_needed':
                return item.date_needed ?? '';
              default:
                return item[column] ?? '';
            }
          }
        });

      })
      .catch(console.error);
  }

  // Initial load
  loadData();
  enableTableSorting(".table");
</script>