<?php include './components/reusable/tablesorting.php'; ?>
<?php include './components/reusable/tablepagination.php'; ?>
<?php include './components/reusable/qrcodeScanner.php'; ?>
<?php include './components/reusable/searchfilter.php'; ?>


<script src="assets/js/bootstrap.bundle.min.js"></script>



<div class="page-content">
  <nav class="page-breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="#">Pages</a></li>
      <li class="breadcrumb-item" aria-current="page">QC To-do List Section</li>
    </ol>
  </nav>

  <div class="row">
    <div class="col-md-12 grid-margin stretch-card">
      <div class="card">
        <div class="card-body">

          <div class="d-flex align-items-center justify-content-between mb-2">
            <h6 class="card-title">To-do List</h6>
            <small id="last-updated" class="text-muted" style="font-size:13px;"></small>
          </div>
          <div class="row mb-3">
            <div class="col-md-3">
              <select id="filter-column" class="form-select">
                <option value="" disabled selected>Select Column to Filter</option>
                <option value="material_no">Material No</option>
                <option value="model">Model</option>
                <option value="shift">Shift</option>
                <option value="lot_no">Lot No</option>
                <option value="total_quantity">Quantity</option>
                <option value="person_incharge">Person Incharge</option>
                <option value="date_needed">Date Needed</option>
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
                <th style="width: 20%; text-align: center;">Material Description <span class="sort-icon"></span></th>
                <th style="width: 8%; text-align: center;">Model <span class="sort-icon"></span></th>
                <th style="width: 8%; text-align: center;">Shift <span class="sort-icon"></span></th>
                <th style="width: 6%; text-align: center;">Lot No <span class="sort-icon"></span></th>
                <th style="width: 8%; text-align: center;">Pending QTY <span class="sort-icon"></span></th>
                <th style="width: 8%; text-align: center;">Total QTY <span class="sort-icon"></span></th>
                <th style="width: 15%; text-align: center;">Person Incharge <span class="sort-icon"></span></th>
                <th style="width: 15%; text-align: center;">Date needed <span class="sort-icon"></span></th>
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
  <!-- First Modal: Input Good/Not Good -->
  <div class="modal fade" id="inspectionModal" tabindex="-1" aria-labelledby="inspectionModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="inspectionModalLabel">Inspection Input</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form id="inspectionForm">
            <input type="hidden" id="inspectionModeHidden" />

            <div class="mb-3">
              <label for="goodQty" class="form-label">Good</label>
              <input type="number" class="form-control" id="goodQty" required>
            </div>
            <div class="mb-3">
              <label for="notGoodQty" class="form-label">No Good</label>
              <input type="number" class="form-control" id="notGoodQty" required>
            </div>

            <!-- Hidden inputs -->
            <input type="hidden" id="totalQtyHidden">
            <input type="hidden" id="recordIdHidden">

            <div id="errorMsg" class="text-danger"></div>

            <!-- Rework and Replace section (hidden by default) -->
            <div id="followUpSection" style="display:none; margin-top:1rem;">
              <hr>
              <h6>Rework / Replace Input</h6>
              <div class="mb-3">
                <label for="rework" class="form-label">Rework</label>
                <input type="number" class="form-control" id="rework" value="0" min="0">
              </div>
              <div class="mb-3">
                <label for="replace" class="form-label">Replace</label>
                <input type="number" class="form-control" id="replace" value="0" min="0">
              </div>
              <div id="followUpErrorMsg" class="text-danger"></div>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-primary" onclick="submitInspection()">Submit</button>
        </div>
      </div>
    </div>
  </div>


  <script src="assets/js/sweetalert2@11.js"></script>

  <script>
    let inspectionModalInstance = null;
    let fullDataSet = [];
    let selectedRowData = null;
    let mode = '';

    document.addEventListener('DOMContentLoaded', () => {
      const tbody = document.getElementById('data-body');
      const filterColumnSelect = document.getElementById('filter-column');
      const filterInput = document.getElementById('filter-input');

      let filteredData = [];

      const paginator = createPaginator({
        data: [],
        rowsPerPage: 10,
        paginationContainerId: 'pagination',
        defaultSortFn: (a, b) => {
          const isTimeOut = item =>
            item.person_incharge?.trim() !== '' && item.time_in && item.done_quantity == null;
          const isContinue = item =>
            item.status?.toLowerCase() === 'continue';
          const weight = item => {
            if (isContinue(item)) return 2;
            if (isTimeOut(item)) return 1;
            return 0;
          };
          if (a.reference_no === b.reference_no) {
            return weight(b) - weight(a);
          }
          return a.reference_no.localeCompare(b.reference_no);
        },
        renderPageCallback: (pageData) => {
          tbody.innerHTML = '';
          pageData.forEach(item => {
            if (item.time_in && item.time_out) return;

            let actionHtml = '';
            const hasIncharge = item.person_incharge?.trim() !== '';

            if (hasIncharge) {
              if (item.done_quantity !== null) {
                actionHtml = `<span class="btn btn-sm bg-success">Done</span>`;
              } else if (item.time_in) {
                actionHtml = `<button 
              class="btn btn-sm btn-warning time-out-btn" 
              data-materialid="${item.material_no}" 
              data-item='${JSON.stringify(item).replace(/'/g, "&apos;")}' 
              data-mode="timeOut"
              data-itemid="${item.itemID}"
              data-id="${item.id || ''}"
            >TIME OUT</button>`;
              } else {
                actionHtml = `<button 
              class="btn btn-sm btn-primary time-in-btn" 
              data-materialid="${item.material_no}" 
              data-item='${JSON.stringify(item).replace(/'/g, "&apos;")}' 
              data-mode="timeIn"
              data-itemid="${item.itemID}"
              data-id="${item.id || ''}"
            >TIME IN</button>`;
              }
            } else {
              actionHtml = `<button 
            class="btn btn-sm btn-primary time-in-btn" 
            data-materialid="${item.material_no}" 
            data-item='${JSON.stringify(item).replace(/'/g, "&apos;")}' 
            data-mode="timeIn"
          >TIME IN</button>`;
            }
            const row = document.createElement('tr');
            row.innerHTML = `
          <td style="text-align: center;">${item.material_no || ''}</td>
          <td style="text-align: center;white-space: normal; word-wrap: break-word;">${item.material_description || ''}</td>
          <td style="text-align: center;">${item.model || ''}</td>
          <td style="text-align: center;">${item.shift || ''}</td>
          <td style="text-align: center;">${item.lot_no || ''}</td>
          <td style="text-align: center;"> ${item.pending_quantity != null ? `${item.pending_quantity}` : `${item.total_quantity}`}</td>
          <td style="text-align: center;">${item.total_quantity}</td>
          <td style="text-align: center;">${item.person_incharge || '<i>NONE</i>'}</td>
          <td style="text-align: center;">${item.date_needed || '<i>NONE</i>'}</td>
          <td style="text-align: center;">${actionHtml}</td>
        `;
            tbody.appendChild(row);
          });

          const now = new Date();
          document.getElementById('last-updated').textContent = `Last updated: ${now.toLocaleString()}`;
        }
      });

      fetch('api/qc/getTodoList.php')
        .then(response => response.json())
        .then(data => {
          fullDataSet = data;

          paginator.setData(fullDataSet); // ✅ Initial render

          setupSearchFilter({
            filterColumnSelector: '#filter-column',
            filterInputSelector: '#filter-input',
            data: fullDataSet,
            onFilter: (filtered) => paginator.setData(filtered),
            customValueResolver: (item, column) => {
              switch (column) {
                case 'material_no':
                  return item.material_no ?? '';
                case 'model':
                  return item.model ?? '';
                case 'shift':
                  return item.shift ?? '';
                case 'lot_no':
                  return item.lot_no?.toString() ?? '';
                case 'total_quantity':
                  return item.total_quantity?.toString() ?? '';
                case 'person_incharge':
                  return item.person_incharge ?? '';
                case 'date_needed':
                  return item.date_needed ?? '';
                default:
                  return item[column] ?? '';
              }
            }
          });
        })
        .catch(err => console.error('Fetch error:', err));



      document.addEventListener('click', function(event) {
        const itemData = event.target.getAttribute('data-item');
        mode = event.target.getAttribute('data-mode');
        if (itemData) {
          selectedRowData = JSON.parse(itemData.replace(/&apos;/g, "'")); // assuming selectedRowData is global
        }
        console.log(mode)
        if (event.target.matches('.time-in-btn')) {
          const {
            material_no,
            material_description
          } = selectedRowData;

          Swal.fire({
            icon: 'question',
            title: 'Confirm Time-In',
            html: `<b>Material No:</b> ${material_no}<br><b>Component:</b> ${material_description}`,
            showCancelButton: true,
            confirmButtonText: 'Yes, Proceed',
            cancelButtonText: 'Cancel'
          }).then(result => {
            if (result.isConfirmed) {
              openQRModal(selectedRowData, mode);
            }
          });
        } else if (event.target.matches('.time-out-btn')) {
          const itemData = event.target.getAttribute('data-item');
          if (itemData) {
            const parsedData = JSON.parse(itemData.replace(/&apos;/g, "'"));

            const {
              material_no,
              material_description
            } = parsedData;

            Swal.fire({
              icon: 'question',
              title: 'Confirm Time-Out',
              html: `<b>Material No:</b> ${material_no}<br><b>Component:</b> ${material_description}`,
              showCancelButton: true,
              confirmButtonText: 'Yes, Proceed',
              cancelButtonText: 'Cancel'
            }).then(result => {
              if (result.isConfirmed) {
                // Store mode in hidden input
                document.getElementById('inspectionModeHidden').value = mode;

                // Set hidden fields
                document.getElementById('totalQtyHidden').value = parsedData.total_quantity;
                document.getElementById('recordIdHidden').value = event.target.getAttribute('data-id');

                // Reset form
                document.getElementById('inspectionForm').reset();
                document.getElementById('followUpSection').style.display = 'none';
                document.getElementById('errorMsg').textContent = '';
                document.getElementById('followUpErrorMsg').textContent = '';

                // Show modal
                inspectionModalInstance = new bootstrap.Modal(document.getElementById('inspectionModal'));
                inspectionModalInstance.show();
              }
            });
          }
        }



      });
    });



    document.getElementById('notGoodQty').addEventListener('input', function() {
      const nogood = parseInt(this.value) || 0;
      const followUpSection = document.getElementById('followUpSection');
      const reworkInput = document.getElementById('rework');
      const replaceInput = document.getElementById('replace');
      const followUpError = document.getElementById('followUpErrorMsg');

      followUpError.textContent = '';

      if (nogood > 0) {
        followUpSection.style.display = 'block';

        // Optional: Reset rework/replace to 0
        reworkInput.value = 0;
        replaceInput.value = 0;
      } else {
        followUpSection.style.display = 'none';
        document.getElementById('rework').value = '';
        document.getElementById('replace').value = '';
      }

    });

    function submitInspection() {
      const good = parseInt(document.getElementById('goodQty').value) || 0;
      const nogood = parseInt(document.getElementById('notGoodQty').value) || 0;
      const inputQty = good + nogood; // Calculate instead of getting from input
      const mode = document.getElementById('inspectionModeHidden').value;
      const followUpSection = document.getElementById('followUpSection');
      const followUpError = document.getElementById('followUpErrorMsg');
      console.log(mode);

      followUpError.textContent = '';

      if (!selectedRowData) {
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'No selected item data.'
        });
        return;
      }

      if (inputQty > selectedRowData.total_quantity) {
        Swal.fire({
          icon: 'error',
          title: 'Invalid Quantity',
          text: `Total (Good + No Good = ${inputQty}) must not exceed total quantity (${selectedRowData.total_quantity}).`
        });
        return;
      }

      if (nogood > 0) {
        followUpSection.style.display = 'block';

        const rework = parseInt(document.getElementById('rework').value) || 0;
        const replace = parseInt(document.getElementById('replace').value) || 0;

        if ((rework + replace) !== nogood) {
          followUpError.textContent = `Rework + Replace must equal No Good (${nogood}).`;
          return;
        }
      } else {
        followUpSection.style.display = 'none';
      }

      const sameReferenceItems = fullDataSet.filter(item => item.reference_no === selectedRowData.reference_no);
      const sumDoneQuantity = sameReferenceItems.reduce((sum, item) => sum + (item.done_quantity || 0), 0);
      const maxTotalQuantity = Math.max(...sameReferenceItems.map(item => item.total_quantity || 0));

      if (sumDoneQuantity + inputQty > maxTotalQuantity) {
        Swal.fire({
          icon: 'error',
          title: 'Quantity Exceeded',
          text: `Total done quantity (${sumDoneQuantity}) plus entered quantity (${inputQty}) exceeds maximum allowed total quantity (${maxTotalQuantity}) for this reference number.`
        });
        return;
      }

      const timeoutData = {
        recordId: document.getElementById('recordIdHidden').value,
        quantity: inputQty,
        good,
        nogood,
        rework: parseInt(document.getElementById('rework').value) || 0,
        replace: parseInt(document.getElementById('replace').value) || 0
      };

      openQRModal(selectedRowData, mode, timeoutData);

      inspectionModalInstance.hide();
    }



    function openQRModal(selectedRowData, mode, timeoutData) {
      const section = "QC";
      scanQRCodeForUser({
        section,
        onSuccess: ({
          user_id,
          full_name
        }) => {
          console.log(selectedRowData, mode, full_name)
          if (mode === 'timeOut') {
            const expectedPersonInCharge = selectedRowData.person_incharge || '';
            if (full_name !== expectedPersonInCharge) {
              Swal.fire({
                icon: 'warning',
                title: 'Person In-Charge Mismatch',
                text: `Scanned name "${full_name}" does not match assigned person "${expectedPersonInCharge}".`,
                confirmButtonText: 'OK'
              });
              return;
            }
          }

          let data = {
            name: full_name,
            id: selectedRowData.id,
            total_quantity: selectedRowData.total_quantity,
            model: selectedRowData.model,
            shift: selectedRowData.shift,
            lot_no: selectedRowData.lot_no,
            date_needed: selectedRowData.date_needed,
            reference_no: selectedRowData.reference_no,
            material_no: selectedRowData.material_no,
            material_description: selectedRowData.material_description
          };

          let url = '';

          if (mode === 'timeIn') {
            url = 'api/qc/timeinOperator.php';
          } else {
            data.quantity = timeoutData.good + timeoutData.nogood;

            data.good = timeoutData.good;
            data.nogood = timeoutData.nogood;
            data.replace = timeoutData.replace;
            data.rework = timeoutData.rework;
            data.pending_quantity = selectedRowData.pending_quantity;
            url = 'api/qc/timeoutOperator.php';
          }

          fetch(url, {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json'
              },
              body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(result => {
              if (result.success === true) {
                Swal.fire({
                  icon: 'success',
                  title: 'Success',
                  text: 'Operation completed successfully!',
                  confirmButtonColor: '#3085d6'
                });
              } else {
                Swal.fire('Error', 'Submission failed.', 'error');
              }
            })
            .catch(error => {
              console.error('Submission error:', error);
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