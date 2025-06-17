<?php include './components/reusable/tablesorting.php'; ?>
<?php include './components/reusable/tablepagination.php'; ?>
<?php include './components/reusable/qrcodeScanner.php'; ?>



<script src="assets/js/bootstrap.bundle.min.js"></script>

<script src="assets/js/html5.qrcode.js" type="text/javascript"></script>

<div class="page-content">
  <nav class="page-breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="#">Pages</a></li>
      <li class="breadcrumb-item" aria-current="page">Assembly Rework Section</li>
    </ol>
  </nav>

  <div class="row">
    <div class="col-md-12 grid-margin stretch-card">
      <div class="card">
        <div class="card-body">

          <div class="d-flex align-items-center justify-content-between mb-2">
            <h6 class="card-title">Reworked Material</h6>
            <small id="last-updated" class="text-muted" style="font-size:13px;"></small>
          </div>

          <div class="row mb-3">
            <div class="col-md-3">
              <select id="filter-column" class="form-select">
                <option value="" disabled selected>Select Column to Filter</option>
                <option value="model">Model</option>
                <option value="material_no">Material No</option>
                <option value="lot_no">Lot No</option>
                <option value="shift">Shift</option>
                <option value="quantity">Quantity</option>
                <option value="qc_person_incharge">Person Incharge</option>
                <option value="qc_timein">Time In</option>
                <option value="qc_timeout">Time Out</option>
              </select>
            </div>
            <div class="col-md-4">
              <input
                type="text"
                id="filter-input"
                class="form-control"
                placeholder="Type to filter..."
                disabled />
            </div>
          </div>
          <table class="table" style="table-layout: fixed; width: 100%;">
            <thead>
              <tr>
                <th style="width: 15%; text-align: center;">Material No <span class="sort-icon"></span></th>
                <th style="width: 5%; text-align: center;">Model <span class="sort-icon"></span></th>
                <th style="width: 8%; text-align: center;">Shift <span class="sort-icon"></span></th>
                <th style="width: 8%; text-align: center;">Lot <span class="sort-icon"></span></th>
                <th style="width: 8%; text-align: center;">Pending Qty <span class="sort-icon"></span></th>
                <th style="width: 8%; text-align: center;">Total Qty <span class="sort-icon"></span></th>
                <th style="width: 15%; text-align: center;">Person Incharge <span class="sort-icon"></span></th>
                <th style="width: 15%; text-align: center;">Time In | Time out <span class="sort-icon"></span></th>
              </tr>
            </thead>

            <tbody id="data-body" style="word-wrap: break-word; white-space: normal;"></tbody>
          </table>
          <div id="pagination" class="mt-3 d-flex justify-content-center"></div>


        </div>
      </div>
    </div>
  </div>
  <!-- QR Code Modal -->
  <div class="modal fade" id="qrModal" tabindex="-1" aria-labelledby="qrModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="qrModalLabel">QR Code Scanner</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div id="qr-reader" style="width:100%"></div>
          <div id="qr-result" class="mt-3 fw-bold text-primary">Waiting for QR scan...</div>
        </div>
      </div>
    </div>
  </div>
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
              <label for="displayTotalQty" class="form-label">Total Quantity</label>
              <input type="number" class="form-control" id="displayTotalQty" required>
            </div>

            <div class="mb-3">
              <label for="good" class="form-label">Good</label>
              <input type="number" class="form-control" id="good" required>
            </div>
            <div class="mb-3">
              <label for="no_good" class="form-label">No Good</label>
              <input type="number" class="form-control" id="no_good" required>
            </div>

            <input type="hidden" id="totalQtyHidden">
            <input type="hidden" id="recordIdHidden">
            <div id="errorMsg" class="text-danger"></div>
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
    let url = '';
    let selectedRowData = null;
    let inspectionModal = null;

    document.addEventListener('DOMContentLoaded', function() {
      let fullData = [];

      const paginator = createPaginator({
        data: [],
        rowsPerPage: 10,
        paginationContainerId: 'pagination',
        defaultSortFn: (a, b) => {
          const weight = item => {
            if (item.qc_timein && !item.qc_timeout) return 2;
            if (!item.qc_timein) return 1;
            return 0;
          };
          return weight(b) - weight(a);
        },
        renderPageCallback: (pageData, currentPage) => {
          const tbody = document.getElementById('data-body');
          tbody.innerHTML = '';

          pageData.forEach(item => {
            let actionHtml = '';

            if (!item.qc_timein) {
              actionHtml = `
            <button 
              class="btn btn-sm btn-success time-in-btn" 
              data-materialid="${item.material_no}"
              data-item='${JSON.stringify(item).replace(/'/g, "&apos;")}'
              data-mode="timeIn"
              data-id="${item.id}"
            >
              TIME IN
            </button>`;
            } else if (!item.qc_timeout) {
              actionHtml = `
            <button 
              class="btn btn-sm btn-warning time-out-btn" 
              data-materialid="${item.material_no}"
              data-item='${JSON.stringify(item).replace(/'/g, "&apos;")}'
              data-mode="timeOut"
              data-id="${item.id}"
            >
              TIME OUT
            </button>`;
            } else {
              actionHtml = `<span class="text-muted">Done</span>`;
            }

            const tr = document.createElement('tr');
            tr.innerHTML = `
          <td style="text-align: center;">${item.material_no || '-'}</td>
          <td style="text-align: center;">${item.model || '-'}</td>
          <td style="text-align: center;">${item.shift || '-'}</td>
          <td style="text-align: center;">${item.lot_no || '-'}</td>
          <td style="text-align: center;">${item.qc_quantity}</td>
          <td style="text-align: center;">${item.quantity}</td>
          <td style="text-align: center;">${item.qc_person_incharge || '-'}</td>
          <td style="text-align: center;">${actionHtml}</td>
        `;

            tbody.appendChild(tr);
          });

          const now = new Date();
          document.getElementById('last-updated').textContent = `Last updated: ${now.toLocaleString()}`;
        }
      });

      function loadTable() {
        fetch('api/qc/getRework.php')
          .then(response => response.json())
          .then(data => {
            fullData = data;
            paginator.setData(fullData);
          })
          .catch(error => {
            console.error('Fetch error:', error);
          });
      }

      // Filter logic
      const filterColumn = document.getElementById('filter-column');
      const filterInput = document.getElementById('filter-input');

      filterColumn.addEventListener('change', () => {
        filterInput.disabled = !filterColumn.value;
        filterInput.value = '';
        applyFilter();
      });

      filterInput.addEventListener('input', () => {
        applyFilter();
      });

      function applyFilter() {
        const column = filterColumn.value;
        const searchTerm = filterInput.value.trim().toLowerCase();

        if (!column || !searchTerm) {
          paginator.setData(fullData);
          return;
        }

        const filtered = fullData.filter(item => {
          let value = item[column];
          if (value === undefined || value === null) return false;
          return String(value).toLowerCase().includes(searchTerm);
        });

        paginator.setData(filtered);
      }

      loadTable();
    });

    document.addEventListener('click', function(event) {
      if (event.target.classList.contains('time-in-btn') || event.target.classList.contains('time-out-btn')) {
        const button = event.target;
        const materialId = button.getAttribute('data-materialid');
        selectedRowData = JSON.parse(button.getAttribute('data-item').replace(/&apos;/g, "'"));
        const mode = button.getAttribute('data-mode');
        const id = button.getAttribute('data-id');

        console.log('Material ID:', materialId);
        console.log('Mode:', mode);
        console.log('Record ID:', id);
        console.log(selectedRowData);
        if (mode === 'timeIn') {
          openQRModal(selectedRowData, mode);
        } else if (mode === 'timeOut') {
          document.getElementById('recordIdHidden').value = selectedRowData.id;
          document.getElementById('totalQtyHidden').value = selectedRowData.quantity;

          // Set values for editable inputs
          // Clear any previous error messages
          document.getElementById('errorMsg').textContent = '';

          // Show the modal
          inspectionModal = new bootstrap.Modal(document.getElementById('inspectionModal'));
          inspectionModal.show();
        }
      }
    });

    function submitInspection() {
      const good = parseInt(document.getElementById('good').value, 10) || 0;
      const no_good = parseInt(document.getElementById('no_good').value, 10) || 0;
      const quantity = parseInt(document.getElementById('displayTotalQty').value, 10) || 0;

      console.log(quantity, good, no_good);

      if (selectedRowData.qc_quantity > 0) {
        if (quantity > selectedRowData.qc_quantity) {
          Swal.fire({
            icon: 'error',
            title: 'Invalid Quantity',
            text: `Quantity must be less than or equal to ${selectedRowData.qc_quantity}.`
          });
          return;
        }
      } else {
        if (quantity > selectedRowData.quantity) {
          Swal.fire({
            icon: 'error',
            title: 'Invalid Quantity',
            text: `Quantity must be less than or equal to ${selectedRowData.quantity}.`
          });
          return;
        }
      }

      if (no_good > 0) {
        Swal.fire({
          icon: 'error',
          title: 'Invalid No Good Value',
          text: 'There must be No Good anymore as it came from the rework already.'
        });
        return;
      }

      if ((good + no_good) !== quantity) {
        Swal.fire({
          icon: 'error',
          title: 'Mismatch Detected',
          text: `Good + No Good must equal ${quantity}.`
        });
        return;
      }

      Swal.fire({
        title: 'Confirm Submission',
        html: `
      <p>Are you sure you want to submit the inspection data?</p>
      <strong>Good:</strong> ${good} <br>
      <strong>No Good:</strong> ${no_good} <br>
      <strong>Total Quantity:</strong> ${quantity}
    `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, Submit',
        cancelButtonText: 'Cancel'
      }).then((result) => {
        if (result.isConfirmed) {
          selectedRowData.good = good;
          selectedRowData.no_good = no_good;
          selectedRowData.inputQty = quantity;

          inspectionModal.hide();
          openQRModal(selectedRowData, 'timeOut');
        }
      });
    }

    function openQRModal(selectedRowData, mode) {
      console.log(selectedRowData, mode);
      
      scanQRCodeForUser({
        onSuccess: ({
          user_id,
          full_name
        }) => {
          const data = {
            id: selectedRowData.id,
            full_name: full_name,
            inputQty: selectedRowData.inputQty,
            no_good: selectedRowData.no_good,
            good: selectedRowData.good,
            reference_no: selectedRowData.reference_no,
            quantity: selectedRowData.quantity,
            qc_pending_quantity: selectedRowData.qc_pending_quantity
          };

          let url = '/mes/api/qc/timein_reworkOperator.php';
          if (mode === 'timeOut') {
            url = '/mes/api/qc/timeout_reworkOperator.php';
          }

          fetch(url, {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json'
              },
              body: JSON.stringify(data)
            })
            .then(res => res.json())
            .then(response => {
              console.log(response);
              if (response.success) {
                Swal.fire({
                  icon: 'success',
                  title: 'Success',
                  text: 'Your operation was successful!',
                  confirmButtonColor: '#3085d6'
                });
              } else {
                Swal.fire('Error', response.message || 'Operation failed.', 'error');
              }
            })
            .catch(err => {
              console.error('Request failed', err);
              Swal.fire('Error', 'Something went wrong.', 'error');
            });
        },
        onCancel: () => {
          console.log('QR scan cancelled or modal closed.');
        }
      });
    }



    enableTableSorting(".table");
  </script>