
<?php
session_start();
$name = $_SESSION['name'] ?? null;
?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>


<div class="page-content">
  <nav class="page-breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="#">Pages</a></li>
      <li class="breadcrumb-item" aria-current="page">Inventory Management</li>
    </ol>
  </nav>

  <div class="row">
    <div class="col-md-12 grid-margin stretch-card">
      <div class="card">
        <div class="card-body">
          <h6 class="card-title">Warehouse Components</h6>

          <div class="d-flex align-items-end gap-3 mb-4">
  <div class="d-flex align-items-end gap-2">
    <label for="filter-select" class="form-label mb-1 w-50">Filter by</label>
  <select id="filter-select" class="form-select form-select-sm" multiple style="min-height: 120px;">
  <option value="status">Status</option>
  <option value="lot_no">Lot No</option>
  <option value="shift">Shift</option>
  <option value="date_needed">Date Needed</option>
  <option value="model">Model</option>
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

<table class="table" style="table-layout: fixed; width: 100%;">
  <thead>
    <tr>
      <th style="width: 10%; text-align: center;">Material No</th>
      <th style="width: 15%; text-align: center;">Material Description</th>
      <th style="width: 7%; text-align: center;">Model</th>
      <th style="width: 7%; text-align: center;">Good</th>
      <th style="width: 7%; text-align: center;">Total Qty</th>
      <th style="width: 10%; text-align: center;">Shift</th>
      <th style="width: 7%; text-align: center;">Lot No</th>
      <th style="width: 15%; text-align: center;">Person Incharge</th>
      <th style="width: 10%; text-align: center;">Date Needed</th>
      <th style="width: 10%; text-align: center;">Section</th>
      <th style="width: 10%; text-align: center;">Status</th>
    </tr>
  </thead>
  <tbody id="data-body" style="word-wrap: break-word; white-space: normal;"></tbody>
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
let allData = [];  // store fetched data globally

function renderTable(data) {
  const tbody = document.getElementById('data-body');
  tbody.innerHTML = '';

  data.forEach(item => {
    const row = document.createElement('tr');

    row.innerHTML = `
      <td style="text-align: center;">${item.material_no || ''}</td>
      <td class="text-center text-truncate" style="max-width: 200px;">${item.material_description || ''}</td>
      <td style="text-align: center;">${item.model || ''}</td>
      <td style="text-align: center;">${item.good || ''}</td>
      <td style="text-align: center;">${item.total_qty || ''}</td>
      <td style="text-align: center;">${item.shift || ''}</td>
      <td style="text-align: center;">${item.lot_no || ''}</td>
      <td style="text-align: center;">${item.person_incharge || '<i>NONE</i>'}</td>
      <td style="text-align: center;">${item.date_needed || ''}</td>
      <td style="text-align: center;">${(item.section || '').toUpperCase()}</td>
  <td style="text-align: center;">
    <button
      class="btn btn-sm ${
        (item.status || '').toUpperCase() === 'DONE' ? 'btn-primary' : 'btn-warning'
      } pull-btn"
      data-id="${item.id}"
      data-good="${item.good || 0}"
      data-total="${item.total_qty || 0}"
    >
      ${(item.status || '').toUpperCase()}
    </button>
  </td>
    `;

    tbody.appendChild(row);
  });

  // Attach event listeners only to non-DONE buttons
  document.querySelectorAll('.pull-btn').forEach(button => {
  const good = parseInt(button.getAttribute('data-good'), 10);
  const total = parseInt(button.getAttribute('data-total'), 10);

  if (!button.classList.contains('btn-primary') && total === good) {
    button.addEventListener('click', () => {
      const id = button.getAttribute('data-id');
      Swal.fire({
        title: 'Are you sure?',
        text: "Do you want to mark this item as pulled out?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, pull out!',
        cancelButtonText: 'Cancel'
      }).then((result) => {
        if (result.isConfirmed) {
          fetch('api/warehouse/pullItem.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id })
          })
          .then(res => res.json())
          .then(response => {
            if(response.success) {
              Swal.fire('Pulled out!', response.message || 'Item marked as pulled out.', 'success');
              loadTable(); // reload full data
            } else {
              Swal.fire('Error', response.message || 'Failed to pull out item.', 'error');
            }
          })
          .catch(() => {
            Swal.fire('Error', 'Network or server error occurred.', 'error');
          });
        }
      });
    });
  }
});

}

function loadTable() {
  fetch('api/warehouse/getItems.php')
    .then(response => response.json())
    .then(data => {
      console.log(data)
      allData = data;  // cache all data
      renderTable(allData);
    })
    .catch(error => {
      console.error('Error loading data:', error);
    });
}

function filterTable() {
  const filterSelect = document.getElementById('filter-select');
  // Get all selected options values as an array
  const selectedFields = Array.from(filterSelect.selectedOptions).map(opt => opt.value);

  const filterVal = document.getElementById('filter-input').value.trim().toLowerCase();

  // If no search value or no selected fields, show all
  if (!filterVal || selectedFields.length === 0) {
    renderTable(allData);
    return;
  }

  // Filter data where any of the selected fields contain the filter value
  const filtered = allData.filter(item => {
    return selectedFields.some(field => {
      const value = (item[field] || '').toString().toLowerCase();
      return value.includes(filterVal);
    });
  });

  renderTable(filtered);
}


// Attach filter input event listeners
document.getElementById('filter-select').addEventListener('change', filterTable);
document.getElementById('filter-input').addEventListener('input', filterTable);

// Initial load
loadTable();

</script>
