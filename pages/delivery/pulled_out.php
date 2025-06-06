<script src="https://unpkg.com/html5-qrcode"></script>

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
          <h6 class="card-title">List of Pulled Out</h6>

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
      <th style="width: 10%; text-align: center;">Material No</th>
      <!-- <th style="width: 20%; text-align: center;">Description</th> -->
      <th style="width: 10%; text-align: center;">Model</th>
      <th style="width: 8%; text-align: center;">Qty</th>
      <th style="width: 8%; text-align: center;">Supplement</th>
      <th style="width: 8%; text-align: center;">Total Qty</th>
      <th style="width: 8%; text-align: center;">Shift</th>
      <th style="width: 8%; text-align: center;">Lot</th>
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
let originalData = [];



function renderTable(data) {
  const tbody = document.getElementById('data-body');
  tbody.innerHTML = '';

  // Sort by date_needed descending (latest first)
  const sortedData = [...data].sort((a, b) => {
    const dateA = new Date(a.date_needed);
    const dateB = new Date(b.date_needed);
    return dateB - dateA; // Newest first
  });

  sortedData.forEach(item => {
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


document.getElementById('column-select').addEventListener('change', dynamicSearch);
document.getElementById('search-input').addEventListener('input', dynamicSearch);

function dynamicSearch() {
  const column = document.getElementById('column-select').value;
  const searchText = document.getElementById('search-input').value.trim().toLowerCase();

  if (!column || !searchText) {
    renderTable(originalData); // Show full data
    return;
  }

  const filtered = originalData.filter(item => {
    const value = (item[column] ?? '').toString().toLowerCase();
    return value.includes(searchText);
  });

  renderTable(filtered);
}


// Initial load
document.addEventListener('DOMContentLoaded', function () {
  fetch('api/delivery/getPulled_out.php')
    .then(response => response.json())
    .then(data => {
    console.log(data)
      originalData = data;
    
      renderTable(data);
    })
    .catch(error => console.error('Error fetching data:', error));



function resetSearch() {
  document.getElementById('column-select').value = '';
  document.getElementById('search-input').value = '';
  renderTable(originalData);
}

});
</script>
