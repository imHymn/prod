<script src="https://unpkg.com/html5-qrcode"></script>

<div class="page-content">
  <nav class="page-breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="#">Pages</a></li>
      <li class="breadcrumb-item" aria-current="page">Delivery Restocked Section</li>
    </ol>
  </nav>

  <div class="row">
    <div class="col-md-12 grid-margin stretch-card">
      <div class="card">
        <div class="card-body">
          <h6 class="card-title">Restocked Materials</h6>

      <div class="d-flex align-items-end gap-3 mb-4">
  <div class="d-flex align-items-end gap-2">
    <label for="filter-select" class="form-label mb-1 w-50">Filter by</label>
    <select id="filter-select" class="form-select form-select-sm">
      <option value="shift">Shift</option>
      <option value="date_needed">Date Needed</option>
      <option value="lot_no">Lot No</option>
    </select>
  </div>
  <div class="d-flex align-items-end gap-2">
    <label for="filter-input" class="form-label mb-1">Search</label>
    <input
      type="text"
      id="filter-input"
      class="form-control form-control-sm"
      placeholder="Enter value..."
      style="min-width: 200px;"
    >
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
            <th style="width: 8%; text-align: center;">Section</th>
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

document.getElementById('filter-input').addEventListener('input', function () {
  const filterKey = document.getElementById('filter-select').value;
  const filterVal = this.value.toLowerCase();

  const filtered = originalData.filter(item => {
    const fieldVal = item[filterKey];
    return fieldVal && String(fieldVal).toLowerCase().includes(filterVal);
  });

  renderTable(filtered);
});


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
      <td style="text-align: center;">${
  item.section === 'QC' && !item.person_incharge_qc 
    ? 'PENDING' 
    : item.status.toUpperCase()
}</td>

<td style="text-align: center;">${item.section.toUpperCase()}</td>


<td style="text-align: center;">${item.date_needed || '<i>NONE</i>'}</td>

    `;
    tbody.appendChild(row);
  });
}



// Initial load
// Initial load
document.addEventListener('DOMContentLoaded', function () {
  fetch('api/delivery/getRestocked.php')
    .then(response => response.json())
    .then(data => {
      console.log(data)
  

    
      renderTable(data);
    })
    .catch(error => console.error('Error fetching data:', error));
});

</script>
