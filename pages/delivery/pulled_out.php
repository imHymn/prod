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
});
</script>
