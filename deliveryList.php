<script src="https://unpkg.com/html5-qrcode"></script>

<div class="page-content">
  <nav class="page-breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="#">Pages</a></li>
      <li class="breadcrumb-item" aria-current="page">Assembly Form</li>
    </ol>
  </nav>

  <div class="row">
    <div class="col-md-12 grid-margin stretch-card">
      <div class="card">
        <div class="card-body">
          <h6 class="card-title">Delivery List</h6>
<div class="d-flex flex-wrap align-items-center mb-3" style="gap: 2.5rem;">
  <div class="d-flex flex-column" style="min-width: 100px;">
    <label for="filter-model" class="form-label mb-1">Model</label>
    <select id="filter-model" class="form-select form-select-sm">
      <option value="">All</option>
    </select>
  </div>

  <div class="d-flex flex-column" style="min-width: 100px;">
    <label for="filter-date" class="form-label mb-1">Date</label>
    <select id="filter-date" class="form-select form-select-sm">
      <option value="">All</option>
    </select>
  </div>

  <div class="d-flex flex-column" style="min-width: 100px;">
    <label for="filter-shift" class="form-label mb-1">Shift</label>
    <select id="filter-shift" class="form-select form-select-sm">
      <option value="">All</option>
    </select>
  </div>

  <div class="d-flex flex-column pe-3" style="min-width: 100px; ">
    <label for="filter-status" class="form-label mb-1">Status</label>
    <select id="filter-status" class="form-select form-select-sm">
      <option value="">All</option>
    </select>
  </div>

  <!-- Separator line added on the previous container's right border -->

  <div class="d-flex flex-column ps-3" style="min-width: 130px;">
    <label for="column-select" class="form-label mb-1">Filter by</label>
    <select id="column-select" class="form-select form-select-sm">
      <option value="material_no">Material No</option>
      <option value="model_name">Model</option>
      <option value="lot_no">Lot</option>
      <option value="shift">Shift</option>
      <option value="section">Status</option>
      <option value="handler_name">Handler Name</option>
    </select>
  </div>

  <div class="d-flex flex-column" style="min-width: 150px;">
    <label for="column-input" class="form-label mb-1">Search</label>
    <input type="text" id="column-input" class="form-control form-control-sm" placeholder="Search...">
  </div>
   <div class="d-flex flex-column" style="min-width: 100px; margin-top: 1.75rem;">
    <button id="clear-filters" class="btn btn-outline-secondary btn-sm" style="white-space: nowrap;">Reset Filters</button>
  </div>
</div>




<table class="table table" style="table-layout: fixed; width: 100%;">
  <thead>
    <tr>
      <th style="width: 15%; text-align: center;">Material No</th>
      <!-- <th style="width: 20%; text-align: center;">Description</th> -->
      <th style="width: 15%; text-align: center;">Model</th>
      <th style="width: 8%; text-align: center;">Qty</th>
      <th style="width: 8%; text-align: center;">Supplement</th>
      <th style="width: 8%; text-align: center;">Total Qty</th>
      <th style="width: 8%; text-align: center;">Shift</th>
      <th style="width: 8%; text-align: center;">Lot</th>
      <th style="width: 8%; text-align: center;">Status</th>
      <th style="width: 25%; text-align: center;">Handler Name</th>
      <th style="width: 25%; text-align: center;">Date Needed</th>
    </tr>
  </thead>
  <tbody id="data-body"></tbody>
</table>

      
      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="qrModal" tabindex="-1" aria-labelledby="qrModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="qrModalLabel">Scan QR Code</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" onclick="stopQRScanner()"></button>
      </div>
      <div class="modal-body">
        <div id="qr-reader" style="width: 100%"></div>
        <div id="qr-result" class="mt-3 text-center fw-bold text-success"></div>
      </div>
    </div>
  </div>
</div>

<script src="assets/js/sweetalert2@11.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />
<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
<script>

</script>
<script>
let html5QrcodeScanner =null;

document.addEventListener('DOMContentLoaded', function () {
    fetch('api/assembly/getDeliveryforms.php')
        .then(response => response.json())
        .then(data => {
            const tbody = document.getElementById('data-body');
            data.forEach(item => {
                const row = document.createElement('tr');
      row.innerHTML = `
  <td style="text-align: center;">${item.material_no}</td>
  <!-- <td style="text-align: center;">${item.material_description}</td> -->
  <td style="text-align: center;">${item.model_name}</td>
  <td style="text-align: center;">${item.quantity}</td>
  <td style="text-align: center;">${item.supplement_order ?? '0'}</td>
  <td style="text-align: center;">${item.total_quantity}</td>
  <td style="text-align: center;">${item.shift}</td>
  <td style="text-align: center;">${item.lot_no}</td>
<td >${ item.section.toUpperCase()}
</td>
<td style="text-align: center;">${item.handler_name || '<i>NONE</i>'}</td>
<td style="text-align: center;">${item.date_needed || '<i>NONE</i>'}</td>


`;



                tbody.appendChild(row);
            });

            // Attach event listeners to TIME IN buttons
            document.querySelectorAll('.time-in-btn').forEach(btn => {
                btn.addEventListener('click', function () {
                    const materialId = this.getAttribute('data-id');
                    openQRModal(materialId);
                });
            });
        })
        .catch(error => console.error('Error fetching data:', error));
});

</script>


<script>
let originalData = [];

function populateFilterOptions(data) {
  const unique = (key) => [...new Set(data.map(item => item[key]).filter(Boolean))];

  const fillSelect = (id, values) => {
    const select = document.getElementById(id);
    select.innerHTML = '<option value="">All</option>';
    values.forEach(val => {
      const opt = document.createElement('option');
      opt.value = val;
      opt.textContent = val;
      select.appendChild(opt);
    });
  };

  fillSelect('filter-model', unique('model_name'));
  fillSelect('filter-date', unique('date_needed'));
  fillSelect('filter-shift', unique('shift'));
  fillSelect('filter-status', unique('section').map(v => v.toUpperCase()));
}

function renderTable(data) {
  const tbody = document.getElementById('data-body');
  tbody.innerHTML = '';
  data.forEach(item => {
    const row = document.createElement('tr');
    row.innerHTML = `
      <td style="text-align: center;">${item.material_no}</td>
      <td style="text-align: center;">${item.model_name}</td>
      <td style="text-align: center;">${item.quantity}</td>
      <td style="text-align: center;">${item.supplement_order ?? '0'}</td>
      <td style="text-align: center;">${item.total_quantity}</td>
      <td style="text-align: center;">${item.shift}</td>
      <td style="text-align: center;">${item.lot_no}</td>
      <td style="text-align: center;">${item.section.toUpperCase()}</td>
      <td style="text-align: center;">${item.handler_name || '<i>NONE</i>'}</td>
      <td style="text-align: center;">${item.date_needed || '<i>NONE</i>'}</td>
    `;
    tbody.appendChild(row);
  });
}

function applyHierarchicalFilters() {
  const model = document.getElementById('filter-model').value;
  const date = document.getElementById('filter-date').value;
  const shift = document.getElementById('filter-shift').value;
  const status = document.getElementById('filter-status').value;

  let filtered = originalData;

  if (model) filtered = filtered.filter(d => d.model_name === model);
  if (date) filtered = filtered.filter(d => d.date_needed === date);
  if (shift) filtered = filtered.filter(d => d.shift === shift);
  if (status) filtered = filtered.filter(d => d.section.toUpperCase() === status);

  renderTable(filtered);
}

// Event listeners for filters
['filter-model', 'filter-date', 'filter-shift', 'filter-status'].forEach(id => {
  document.getElementById(id).addEventListener('change', applyHierarchicalFilters);
});

document.getElementById('column-input').addEventListener('input', function () {
  const col = document.getElementById('column-select').value;
  const val = this.value.toLowerCase();

  const filtered = originalData.filter(item => {
    // Ensure the field exists and convert to string before .toLowerCase()
    const fieldVal = item[col];
    if (!fieldVal) return false; // no match if falsy (null/undefined)

    return fieldVal.toString().toLowerCase().includes(val);
  });

  renderTable(filtered);
});


// Initial load
document.addEventListener('DOMContentLoaded', function () {
  fetch('api/assembly/getDeliveryforms.php')
    .then(response => response.json())
    .then(data => {
      originalData = data;
      populateFilterOptions(data);
      renderTable(data);
    })
    .catch(error => console.error('Error fetching data:', error));
});
</script>
