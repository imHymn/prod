<?php include './components/reusable/tablesorting.php'; ?>
<?php include './components/reusable/tablepagination.php'; ?>
<?php include './components/reusable/qrcodeScanner.php'; ?>
<?php include './components/reusable/searchfilter.php'; ?>



<script src="assets/js/bootstrap.bundle.min.js"></script>

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
                <option value="material_no">Material No</option>
                <option value="model">Model</option>
                <option value="shift">Shift</option>
                <option value="lot_no">Lot</option>
                <option value="assembly_pending_quantity">Pending Quantity</option>


                <option value="quantity">Total Qty</option>
                <option value="assembly_person_incharge">Person Incharge</option>
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
                <th style="width: 10%; text-align: center;">Material No <span class="sort-icon"></span></th>
                <th style="width: 20%; text-align: center;white-space: normal; word-wrap: break-word;">Material Description <span class="sort-icon"></span></th>
                <th style="width: 5%; text-align: center;">Model <span class="sort-icon"></span></th>
                <th style="width: 8%; text-align: center;">Shift <span class="sort-icon"></span></th>
                <th style="width: 5%; text-align: center;">Lot <span class="sort-icon"></span></th>
                <th style="width: 8%; text-align: center;">Pending Qty <span class="sort-icon"></span></th>
                <th style="width: 8%; text-align: center;">Total Qty <span class="sort-icon"></span></th>
                <th style="width: 15%; text-align: center;">Person Incharge <span class="sort-icon"></span></th>
                <th style="width: 10%; text-align: center;">Date Needed <span class="sort-icon"></span></th>
                <th style="width: 10%; text-align: center;">Time In | Time out <span class="sort-icon"></span></th>
              </tr>
            </thead>

            <tbody id="data-body" style="word-wrap: break-word; white-space: normal;"></tbody>
          </table>

          <nav aria-label="Page navigation" class="mt-3">
            <ul class="pagination justify-content-center" id="pagination"></ul>
          </nav>

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
              <label for="rework" class="form-label">Rework</label>
              <input type="number" class="form-control" id="rework" required>
            </div>
            <div class="mb-3">
              <label for="replace" class="form-label">Replace</label>
              <input type="number" class="form-control" id="replace" required>
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
    let fullDataSet = []; // Still global if needed
    let paginator = null;

    document.addEventListener('DOMContentLoaded', function() {
      const tbody = document.getElementById('data-body');
      const filterColumnSelect = document.getElementById('filter-column');
      const filterInput = document.getElementById('filter-input');
      const paginationContainerId = 'pagination';
      const rowsPerPage = 10;

      fetch('api/assembly/getRework.php')
        .then(response => response.json())
        .then(data => {
          fullDataSet = data;
          filteredData = [...fullDataSet];
          console.log(fullDataSet)
          // Initialize paginator
          paginator = createPaginator({
            data: filteredData,
            rowsPerPage,
            renderPageCallback: renderTable,
            paginationContainerId,
            defaultSortFn: sortByPriority
          });

          paginator.render();

          // ✅ Apply reusable search filter
          setupSearchFilter({
            filterColumnSelector: '#filter-column',
            filterInputSelector: '#filter-input',
            data: fullDataSet,
            onFilter: (filtered) => {
              filteredData = filtered;
              paginator.setData(filtered);
            },
            customValueResolver: (item, column) => {
              switch (column) {
                case 'reference_no':
                  return item.reference_no;
                case 'material_no':
                  return item.material_no;
                case 'material_description':
                  return item.material_description;
                case 'model':
                  return item.model;
                case 'lot_no':
                  return item.lot_no;
                case 'status':
                  return item.status;
                case 'person_incharge':
                case 'assembly_person_incharge':
                  return item.assembly_person_incharge ?? '';
                case 'assembly_pending_quantity':
                  return item.assembly_pending_quantity ?? '';
                default:
                  return item[column] ?? '';
              }
            }


          });

        })
        .catch(error => {
          console.error('Fetch error:', error);
        });


      function sortByPriority(a, b) {
        const weight = item => {
          if (item.assembly_timein && !item.assembly_timeout) return 2;
          if (!item.assembly_timein) return 1;
          return 0;
        };
        return weight(b) - weight(a);
      }

      function renderTable(pageData, currentPage) {
        tbody.innerHTML = '';

        pageData.forEach(item => {

          let actionHtml = '';

          if (!item.assembly_timein) {
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
          } else if (!item.assembly_timeout) {
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
        <td style="text-align: center;">${item.material_no}</td>
        <td style="text-align: center;">${item.material_description}</td>
        <td style="text-align: center;">${item.model}</td>
        <td style="text-align: center;">${item.shift}</td>
        <td style="text-align: center;">${item.lot_no}</td>
           <td style="text-align: center;">
          ${item.assembly_quantity}
        </td>
        <td style="text-align: center;">
          ${item.quantity}
        </td>
        <td style="text-align: center;">${item.assembly_person_incharge || '-'}</td>
         <td style="text-align: center;">${item.date_needed || '-'}</td>
        <td style="text-align: center;">${actionHtml}</td>
      `;
          tbody.appendChild(tr);
        });

        document.getElementById('last-updated').textContent = `Last updated: ${new Date().toLocaleString()}`;
      }
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

        const {
          material_no,
          material_description
        } = selectedRowData;

        Swal.fire({
          icon: 'question',
          title: `Confirm ${mode === 'timeIn' ? 'Time-In' : 'Time-Out'}`,
          html: `<b>Material No:</b> ${material_no}<br><b>Component:</b> ${material_description}`,
          showCancelButton: true,
          confirmButtonText: 'Yes, Proceed',
          cancelButtonText: 'Cancel'
        }).then(result => {
          if (result.isConfirmed) {
            if (mode === 'timeIn') {
              openQRModal(selectedRowData, mode);
            } else if (mode === 'timeOut') {
              document.getElementById('recordIdHidden').value = selectedRowData.id;
              document.getElementById('totalQtyHidden').value = selectedRowData.quantity;

              // Reset form and clear messages
              document.getElementById('inspectionForm').reset();
              if (document.getElementById('errorMsg')) {
                document.getElementById('errorMsg').textContent = '';
              }
              if (document.getElementById('followUpErrorMsg')) {
                document.getElementById('followUpErrorMsg').textContent = '';
              }
              if (document.getElementById('followUpSection')) {
                document.getElementById('followUpSection').style.display = 'none';
              }


              // Show the modal
              inspectionModal = new bootstrap.Modal(document.getElementById('inspectionModal'));
              inspectionModal.show();
            }
          }
        });
      }
    });


    function submitInspection() {
      const rework = parseInt(document.getElementById('rework').value, 10) || 0;
      const replace = parseInt(document.getElementById('replace').value, 10) || 0;
      const quantity = rework + replace; // Use this as total quantity

      const not_good = quantity;

      if (selectedRowData.assembly_quantity > 0) {
        if (quantity > selectedRowData.assembly_quantity) {
          Swal.fire({
            icon: 'error',
            title: 'Invalid Quantity',
            text: `Quantity must be less than or equal to ${selectedRowData.assembly_quantity}.`
          });
          return;
        }
      }

      // Optional: you may now skip this check since rework + replace = quantity by definition
      // But we keep it for integrity
      if ((rework + replace) !== not_good) {
        Swal.fire({
          icon: 'error',
          title: 'Mismatch in Rework + Replace',
          text: `Rework + Replace must equal ${not_good}.`
        });
        return;
      }

      Swal.fire({
        title: 'Confirm Submission',
        html: `
      <p>Are you sure you want to submit the inspection data?</p>
      <strong>Rework:</strong> ${rework} <br>
      <strong>Replace:</strong> ${replace} <br>
      <strong>Total Quantity:</strong> ${quantity}
    `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, Submit',
        cancelButtonText: 'Cancel'
      }).then((result) => {
        if (result.isConfirmed) {
          selectedRowData.rework = rework;
          selectedRowData.replace = replace;
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
          // ✅ Only check person-in-charge during timeout
          const expectedPersonInCharge = selectedRowData.assembly_person_incharge || '';
          if (mode === 'timeOut' && full_name !== expectedPersonInCharge) {
            Swal.fire({
              icon: 'warning',
              title: 'Person In-Charge Mismatch',
              text: `Scanned name "${full_name}" does not match assigned person "${expectedPersonInCharge}".`,
              confirmButtonText: 'OK'
            });
            return;
          }

          const data = {
            id: selectedRowData.id,
            full_name: full_name,
            inputQty: selectedRowData.inputQty,
            replace: selectedRowData.replace,
            rework: selectedRowData.rework,
            reference_no: selectedRowData.reference_no,
            quantity: selectedRowData.quantity,
            assembly_pending_quantity: selectedRowData.assembly_pending_quantity
          };

          let url = '/mes/api/assembly/timein_reworkOperator.php';
          if (mode === 'timeOut') {
            url = '/mes/api/assembly/timeout_reworkOperator.php';
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
          console.log('QR scan was cancelled or modal closed.');
        }
      });
    }


    enableTableSorting(".table");
  </script>