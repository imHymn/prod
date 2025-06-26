<?php include './components/reusable/tablesorting.php'; ?>

<script src="assets/js/bootstrap.bundle.min.js"></script>


<div class="page-content">
  <nav class="page-breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="#">Pages</a></li>
      <li class="breadcrumb-item" aria-current="page">FG Restocked Section</li>
    </ol>
  </nav>

  <div class="row">
    <div class="col-md-12 grid-margin stretch-card">
      <div class="card">
        <div class="card-body">

          <div class="d-flex align-items-center justify-content-between mb-2">
            <h6 class="card-title">Ready to be Pulled out</h6>
            <small id="last-updated" class="text-muted" style="font-size:13px;"></small>
          </div>

          <table class="table" style="table-layout: fixed; width: 100%;">
            <thead>
              <tr>
                <th style="width: 10%; text-align: center;">Material No <span class="sort-icon"></span></th>
                <th style="width: 15%; text-align: center;">Material Description <span class="sort-icon"></span></th>
                <th style="width: 7%; text-align: center;">Model <span class="sort-icon"></span></th>
                <th style="width: 7%; text-align: center;">FG <span class="sort-icon"></span></th>
                <th style="width: 7%; text-align: center;">Total Quantity <span class="sort-icon"></span></th>
                <th style="width: 10%; text-align: center;">Shift <span class="sort-icon"></span></th>
                <th style="width: 7%; text-align: center;">Lot No <span class="sort-icon"></span></th>
                <th style="width: 10%; text-align: center;">Date Needed <span class="sort-icon"></span></th>
                <th style="width: 10%; text-align: center;">Pull out <span class="sort-icon"></span></th>
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
    let allData = []; // store fetched data globally

    function renderTable(data) {
      const tbody = document.getElementById('data-body');
      tbody.innerHTML = '';

      data.forEach(item => {
        if (item.status === 'done') return;
        const row = document.createElement('tr');

        row.innerHTML = `
      <td style="text-align: center;">${item.material_no || ''}</td>
      <td class="text-center text-truncate" style="max-width: 200px;">${item.material_description || ''}</td>
      <td style="text-align: center;">${item.model || ''}</td>
           <td style="text-align: center;">${item.quantity || ''}</td>
      <td style="text-align: center;">${item.total_quantity || ''}</td>
      <td style="text-align: center;">${item.shift || ''}</td>
      <td style="text-align: center;">${item.lot_no || ''}</td>
      <td style="text-align: center;">${item.date_needed || ''}</td>
     
  <td style="text-align: center;">
 <button
      class="btn btn-sm ${
        (item.status || '').toUpperCase() === 'DONE' ? 'btn-primary' : 'btn-warning'
      } pull-btn"
      data-id="${item.id}"
      data-quantity="${item.quantity || 0}"
      data-total_quantity="${item.total_quantity || 0}"
      data-material_no="${item.material_no || ''}"
      data-description="${item.material_description || ''}"
      data-reference_no="${item.reference_no || ''}"
    >
      ${(item.status || '').toUpperCase()}
    </button>
  </td>
    `;

        tbody.appendChild(row);
      });
      const now = new Date();
      document.getElementById('last-updated').textContent = `Last updated: ${now.toLocaleString()}`;
      // Attach event listeners only to non-DONE buttons
      document.querySelectorAll('.pull-btn').forEach(button => {
        const quantity = parseInt(button.getAttribute('data-quantity'));
        const total_quantity = parseInt(button.getAttribute('data-total_quantity'));
        const id = button.getAttribute('data-id');
        const material_no = button.getAttribute('data-material_no');
        const material_description = button.getAttribute('data-description');
        const reference_no = button.getAttribute('data-reference_no');
        const model = button.getAttribute('data-model');

        if (!button.classList.contains('btn-primary') && quantity === total_quantity) {
          button.addEventListener('click', () => {
            Swal.fire({
              title: 'Confirm Pull Out',
              html: `
          <p><strong>Material No:</strong> ${material_no}</p>
          <p><strong>Component:</strong> ${material_description}</p>
          <p>Do you want to mark this item as pulled out?</p>
        `,
              icon: 'warning',
              showCancelButton: true,
              confirmButtonText: 'Yes, pull out!',
              cancelButtonText: 'Cancel'
            }).then((result) => {
              if (result.isConfirmed) {
                fetch('api/warehouse/pullItemWarehouse.php', {
                    method: 'POST',
                    headers: {
                      'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                      id,
                      material_no,
                      material_description,
                      total_quantity,
                      reference_no
                    })
                  })
                  .then(res => res.json())
                  .then(response => {
                    if (response.success) {
                      Swal.fire('Pulled out!', response.message || 'Item marked as pulled out.', 'success');
                      loadTable(); // reload full data
                    } else {
                      Swal.fire('Error', response.message || 'Failed to pull out item.', 'error');
                    }
                  })
                  .catch(err => {
                    console.error('Pull out error:', err);
                    Swal.fire('Error', 'An unexpected error occurred.', 'error');
                  });
              }
            });
          });
        }
      });


    }

    function loadTable() {
      fetch('api/warehouse/getPending_pulling.php')
        .then(response => response.json())
        .then(data => {

          allData = data; // cache all data
          renderTable(allData);
        })
        .catch(error => {
          console.error('Error loading data:', error);
        });
    }

    // Initial load
    loadTable();
    enableTableSorting(".table");
  </script>