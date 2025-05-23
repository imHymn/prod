<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>


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

<table class="table table" style="table-layout: fixed; width: 100%;">
<thead>
  <tr>
    <th style="width: 15%; text-align: center;">Material No</th>
    <th style="width: 5%; text-align: center;">Model</th>
    <th style="width: 8%; text-align: center;">Total Qty</th>
    <th style="width: 8%; text-align: center;">Shift</th>
    <th style="width: 8%; text-align: center;">Lot</th>
    <th style="width: 15%; text-align: center;">Handler Name</th>
    <th style="width: 12%; text-align: center;">Time In</th>
    <th style="width: 12%; text-align: center;">Time Out</th>
    <th style="width: 8%; text-align: center;">Status</th>
  </tr>
</thead>

  <tbody id="data-body"></tbody>
</table>

      
      </div>
    </div>
  </div>
</div>
<!-- Inspection Modal -->
<div class="modal fade" id="inspectionModal" tabindex="-1" aria-labelledby="inspectionModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="inspectionModalLabel">Inspection Input</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="inspectionForm">
          <div class="mb-3">
            <label for="goodQty" class="form-label">Good</label>
            <input type="number" class="form-control" id="goodQty" required>
          </div>
          <div class="mb-3">
            <label for="notGoodQty" class="form-label">Not Good</label>
            <input type="number" class="form-control" id="notGoodQty" required>
          </div>
          <input type="hidden" id="totalQtyHidden">
          <div id="errorMsg" class="text-danger"></div>
          <input type="hidden" id="recordIdHidden">

        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" onclick="submitInspection()">Submit</button>
      </div>
    </div>
  </div>
</div>
<!-- SweetAlert2 CDN -->
<script src="assets/js/sweetalert2@11.js"></script>
<script>
fetch('api/assembly/getAssemblyData.php')
  .then(response => response.json())
  .then(data => {
    console.log(data);
    const tbody = document.getElementById('data-body');
    tbody.innerHTML = ''; // clear existing rows
data.forEach(item => {
  const row = document.createElement('tr');

  // Row content
  row.innerHTML = `
    <td style="text-align: center;">${item.material_no || ''}</td>
    <td style="text-align: center;">${item.model || ''}</td>
    <td style="text-align: center;">${item.total_qty || ''}</td>
    <td style="text-align: center;">${item.shift || ''}</td> 
    <td style="text-align: center;">${item.lot_no || ''}</td>
    <td style="text-align: center;">${item.person_incharge || '<i>NONE</i>'}</td>
    <td style="text-align: center;">${item.time_in || ''}</td>
    <td style="text-align: center;">${item.time_out || ''}</td>
 <td style="text-align: center;">
  <button 
    class="btn btn-sm ${item.status_qc === 'pending' ? 'btn-warning' : 'btn-primary'}"
    style="pointer-events: ${item.status_qc === 'pending' ? 'auto' : 'none'};"
  >
    ${item.status_qc.toUpperCase()}
  </button>
</td>

  `;

  // Only make the row clickable if status_qc is 'pending'
  if (item.status_qc === 'pending') {
    row.style.cursor = 'pointer';
    row.addEventListener('click', () => openInspectionModal(item.id, item.total_qty));

  }

  tbody.appendChild(row);
});


  })
  .catch(error => {
    console.error('Error loading data:', error);
  });
function openInspectionModal(id, totalQty) {
  document.getElementById('totalQtyHidden').value = totalQty;
  document.getElementById('recordIdHidden').value = id;

  document.getElementById('inspectionForm').reset();
  document.getElementById('errorMsg').textContent = '';

  const modal = new bootstrap.Modal(document.getElementById('inspectionModal'));
  modal.show();
}
function submitInspection() {
  const goodQty = parseInt(document.getElementById('goodQty').value, 10);
  const notGoodQty = parseInt(document.getElementById('notGoodQty').value, 10);
  const totalQty = parseInt(document.getElementById('totalQtyHidden').value, 10);
  const recordId = document.getElementById('recordIdHidden').value;
  const errorMsg = document.getElementById('errorMsg');

  if (isNaN(goodQty) || isNaN(notGoodQty)) {
    errorMsg.textContent = 'Please enter both quantities.';
    return;
  }

  if ((goodQty + notGoodQty) !== totalQty) {
    errorMsg.textContent = `Total must equal ${totalQty} as Total Pulled Quantity.`;
    return;
  }

  const postData = {
    id: recordId,
    good: goodQty,
    not_good: notGoodQty,
    total_qty: totalQty
  };

  fetch('api/qc/makeRating.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(postData)
  })
  .then(response => response.json())
  .then(result => {
    console.log(result)
    if (result.success=='true') {
      Swal.fire({
          icon: 'success',
          title: 'Success',
          text: 'Your operation was successful!',
          confirmButtonColor: '#3085d6'
        }).then(() => {
          location.reload();
        });
      const modalElement = document.getElementById('inspectionModal');
      const modal = bootstrap.Modal.getInstance(modalElement) || new bootstrap.Modal(modalElement);
      modal.hide();
    } else {
      errorMsg.textContent = result.message || 'Submission failed.';
    }
  })
  .catch(error => {
    console.error('Submission error:', error);
    errorMsg.textContent = 'An error occurred while submitting.';
  });
}



</script>
