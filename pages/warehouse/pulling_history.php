
<?php include './components/reusable/tablesorting.php'; ?>
<?php include './components/reusable/tablepagination.php'; ?>
<script src="assets/js/bootstrap.bundle.min.js"></script>

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
      <div class="d-flex align-items-center justify-content-between mb-2">
  <h6 class="card-title mb-0">Pulled out History</h6>
  <small id="last-updated" class="text-muted" style="font-size:13px;"></small>
</div>

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
            <th style="width: 10%; text-align: center;">Material No <span class="sort-icon"></span></th>
            <th style="width: 15%; text-align: center;">Material Description <span class="sort-icon"></span></th>
            <th style="width: 5%; text-align: center;">Model <span class="sort-icon"></span></th>
            <th style="width: 7%; text-align: center;">Total Quantity <span class="sort-icon"></span></th>
            <th style="width: 7%; text-align: center;">Shift <span class="sort-icon"></span></th>
            <th style="width: 5%; text-align: center;">Lot No <span class="sort-icon"></span></th>
            <th style="width: 10%; text-align: center;">Date Needed <span class="sort-icon"></span></th>
            <th style="width: 10%; text-align: center;">Pulled At <span class="sort-icon"></span></th>
          </tr>
        </thead>
        <tbody id="data-body" style="word-wrap: break-word; white-space: normal;"></tbody>
      </table>


          <!-- Pagination -->
          <div id="pagination" class="d-flex justify-content-center mt-3"></div>

          <!-- Last Updated -->
     

        </div>
      </div>
    </div>
  </div>
</div>

<script src="assets/js/sweetalert2@11.js"></script>
<script>
let fullDataSet = [];

document.addEventListener('DOMContentLoaded', () => {
  const tbody = document.getElementById('data-body');
  const searchInput = document.getElementById('search-input');
  const columnSelect = document.getElementById('column-select');

  searchInput.disabled = true; // disable input until a column is selected

  const paginator = createPaginator({
    data: [],
    rowsPerPage: 10,
    paginationContainerId: 'pagination',
    defaultSortFn: (a, b) => new Date(b.pulled_at) - new Date(a.pulled_at),
    renderPageCallback: (pageData) => {
      tbody.innerHTML = '';
      pageData.forEach(item => {
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

      const now = new Date();
      document.getElementById('last-updated').textContent = `Last updated: ${now.toLocaleString()}`;
    }
  });

  function applyFilter() {
    const column = columnSelect.value;
    const query = searchInput.value.toLowerCase().trim();

    if (!column || !query) {
      paginator.setData(fullDataSet);
      return;
    }

    const filtered = fullDataSet.filter(item => {
      const value = (item[column] ?? '').toString().toLowerCase();
      return value.includes(query);
    });

    paginator.setData(filtered);
  }

  searchInput.addEventListener('input', applyFilter);

  columnSelect.addEventListener('change', () => {
    const hasSelection = !!columnSelect.value;
    searchInput.disabled = !hasSelection;
    searchInput.value = '';
    applyFilter();
  });

  function loadData() {
    fetch('api/controllers/warehouse/getPullingHistory.php')
      .then(res => res.json())
      .then(data => {
        fullDataSet = data;
        paginator.setData(fullDataSet);
      })
      .catch(err => {
        console.error('Error loading data:', err);
      });
  }

  enableTableSorting(".table");
  loadData();
});

</script>
