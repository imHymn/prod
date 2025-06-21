<?php include './components/reusable/tablesorting.php'; ?>
<?php include './components/reusable/tablepagination.php'; ?>
<?php include './components/reusable/qrcodeScanner.php'; ?>
<?php include './components/reusable/searchfilter.php'; ?>

<div class="page-content">
  <nav class="page-breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="#">Pages</a></li>
      <li class="breadcrumb-item" aria-current="page">Assembly To-do List Section</li>
    </ol>
  </nav>

  <div class="row">
    <div class="col-md-12 grid-margin stretch-card">
      <div class="card">
        <div class="card-body">
          <div class="d-flex align-items-center justify-content-between mb-2">
            <h6 class="card-title mb-0">To-do List</h6>
            <small id="last-updated" class="text-muted" style="font-size:13px;"></small>
          </div>

          <div class="row mb-3">
            <div class="col-md-3">
              <select id="filter-column" class="form-select">
                <option value="" disabled selected>Select Column</option>
                <option value="material_no">Material No</option>
                <option value="model">Model</option>
                <option value="shift">Shift</option>
                <option value="lot_no">Lot No</option>
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
                <th style="width: 7%; text-align: center;">Model <span class="sort-icon"></span></th>
                <th style="width: 8%; text-align: center; margin-right:-30px;">Shift <span class="sort-icon"></span></th>
                <th style="width: 5%; text-align: center;">Lot <span class="sort-icon"></span></th>
                <th style="width: 8%; text-align: center;">Pending Qty <span class="sort-icon"></span></th>
                <th style="width: 8%; text-align: center;">Total Qty <span class="sort-icon"></span></th>

                <th style="width: 15%; text-align: center;">Person Incharge <span class="sort-icon"></span></th>
                <th style="width: 10%; text-align: center;">Date needed <span class="sort-icon"></span></th>
                <th style="width: 10%; text-align: center;">Time In | Time out <span class="sort-icon"></span></th>
              </tr>
            </thead>
            <tbody id="data-body"></tbody>
          </table>


          <nav aria-label="Page navigation" class="mt-3">
            <ul class="pagination justify-content-center" id="pagination"></ul>
          </nav>


        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="quantityModal" tabindex="-1" aria-labelledby="quantityModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <form id="quantityForm" class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="quantityModalLabel">Enter Quantity</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <input
            type="number"
            class="form-control"
            id="quantityInput"
            name="quantity"
            min="1"
            placeholder="Enter quantity"
            required />
          <div class="invalid-feedback">
            Please enter a valid quantity (1 or more).
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Submit</button>
        </div>
      </form>
    </div>
  </div>


  <script src="assets/js/sweetalert2@11.js"></script>
  <script src="assets/js/bootstrap.bundle.min.js"></script>
  <script src="assets/js/jquery.min.js"></script>
  <link rel="stylesheet" href="assets/css/choices.min.css" />
  <script src="assets/js/choices.min.js"></script>
  <script>

  </script>
  <script>
    let assemblyData = []; // Global variable
    let currentMaterialId = null;
    let currentItem = null;
    let currentMode = null;
    let timeout_id = null;
    let quantityModal;
    let currentPage = 1;
    const rowsPerPage = 10;
    let paginatedData = [];
    let paginator = null;
    document.addEventListener('DOMContentLoaded', function() {
      quantityModal = new bootstrap.Modal(document.getElementById('quantityModal'));


      fetch('api/delivery/getDeliveryforms.php')
        .then(response => response.json())
        .then(deliveryData => {
          fetch('api/assembly/getTodoList.php')
            .then(response => response.json())
            .then(fetchedAssemblyData => {
              assemblyData = fetchedAssemblyData;

              let filteredDeliveryData = deliveryData.filter(
                item => item.section === 'DELIVERY' || item.section === 'ASSEMBLY'
              );

              // Step 2: Exclude specific material descriptions
              const excludedDescriptions = [
                'COVER ECU',
                '09-MIT-SS3-MB136313-SIDE PANEL,FRT LH',
                '09-MIT-SS3-MB136314-SIDE PANEL,FRT RH'
              ];

              filteredDeliveryData = filteredDeliveryData.filter(item =>
                !excludedDescriptions.includes(item.material_description?.trim())
              );
              const sortFn = (a, b) => {
                const aAssembly = assemblyData.find(x => String(x.itemID) === String(a.id));
                const bAssembly = assemblyData.find(x => String(x.itemID) === String(b.id));

                const aCanTimeout = aAssembly && aAssembly.time_in && !aAssembly.time_out ? 1 : 0;
                const bCanTimeout = bAssembly && bAssembly.time_in && !bAssembly.time_out ? 1 : 0;
                if (aCanTimeout !== bCanTimeout) return bCanTimeout - aCanTimeout;

                const aInProgress = aAssembly && !!aAssembly.time_in ? 1 : 0;
                const bInProgress = bAssembly && !!bAssembly.time_in ? 1 : 0;
                if (aInProgress !== bInProgress) return bInProgress - aInProgress;

                const aContinue = a.status?.toLowerCase() === 'continue' ? 1 : 0;
                const bContinue = b.status?.toLowerCase() === 'continue' ? 1 : 0;
                if (aContinue !== bContinue) return bContinue - aContinue;

                return a.reference_no.localeCompare(b.reference_no);
              };

              paginator = createPaginator({
                data: filteredDeliveryData,
                rowsPerPage: 10,
                paginationContainerId: 'pagination',
                renderPageCallback: renderPaginatedTable,
                defaultSortFn: sortFn
              });

              paginator.render();

              setupSearchFilter({
                filterColumnSelector: '#filter-column',
                filterInputSelector: '#filter-input',
                data: filteredDeliveryData,
                onFilter: (filtered) => paginator.setData(filtered),
                customValueResolver: (item, column) => {
                  switch (column) {
                    case 'material_no':
                      return item.material_no;
                    case 'model':
                      return item.model;
                    case 'shift':
                      return item.shift;
                    case 'lot_no':
                      return item.lot_no;
                    case 'person_incharge':
                      return item.person_incharge;
                    case 'date_needed':
                      return item.date_needed;
                    default:
                      return item[column] ?? '';
                  }
                }
              });


            });
        });

    });

    function renderPaginatedTable(pageData, page) {
      const tbody = document.getElementById('data-body');
      tbody.innerHTML = '';

      pageData.forEach(item => {
        const assemblyRecord = assemblyData.find(a => String(a.itemID) === String(item.id));
        const personInCharge = assemblyRecord?.person_incharge || '<i>NONE</i>';

        let timeStatus = '';
        if (!assemblyRecord) {
          timeStatus = `<button class="btn btn-sm btn-primary time-in-btn"
                      data-item='${JSON.stringify(item).replace(/'/g, "&apos;")}'
                      data-materialid="${item.material_no}"
                      data-itemid="${item.id}"
                      data-mode="timeIn">TIME IN</button>`;
        } else if (!assemblyRecord.time_in) {
          timeStatus = `<button class="btn btn-sm btn-primary time-in-btn"
                      data-item='${JSON.stringify(item).replace(/'/g, "&apos;")}'
                      data-materialid="${item.material_no}"
                      data-itemid="${assemblyRecord.itemID}"
                      data-id="${assemblyRecord.id}"
                      data-mode="timeIn">TIME IN</button>`;
        } else if (assemblyRecord.time_in && !assemblyRecord.time_out) {
          const relatedAssemblyData = assemblyData.filter(a => a.reference_no === item.reference_no);
          timeStatus = `<button class="btn btn-sm btn-warning time-out-btn"
                      data-item='${JSON.stringify(item).replace(/'/g, "&apos;")}'
                      data-materialid="${item.material_no}"
                      data-itemid="${assemblyRecord.itemID}"
                      data-id="${assemblyRecord.id}"
                      data-mode="timeOut"
                      data-assemblyItem='${JSON.stringify(relatedAssemblyData).replace(/'/g, "&apos;")}'>TIME OUT</button>`;
        } else {
          timeStatus = `<span class="btn btn-sm bg-success">DONE</span>`;
        }

        const row = document.createElement('tr');
        row.innerHTML = `
      <td style="text-align: center;">${item.material_no}</td>
      <td style="text-align: center;white-space: normal; word-wrap: break-word;">${item.material_description}</td>
      <td style="text-align: center;">${item.model_name}</td>
      <td style="text-align: center;">${item.shift}</td>
      <td style="text-align: center;">${item.lot_no}</td>
      <td style="text-align: center;"> ${item.assembly_pending != null ? `${item.assembly_pending}` : `${item.total_quantity}`}</td>
      <td style="text-align: center;">${item.total_quantity}</td>
      <td style="text-align: center;">${personInCharge}</td>
      <td style="text-align: center;">${item.date_needed}</td>
      <td style="text-align: center;">${timeStatus}</td>
    `;
        tbody.appendChild(row);
      });

      document.getElementById('last-updated').textContent = `Last updated: ${new Date().toLocaleString()}`;
    }


    const filterColumnSelect = document.getElementById('filter-column');
    const filterInput = document.getElementById('filter-input');
    const tbody = document.getElementById('data-body');
    filterInput.addEventListener('input', filterTable);
    filterColumnSelect.addEventListener('change', () => {
      filterInput.value = '';
      filterInput.disabled = !filterColumnSelect.value;
      filterInput.focus();
      filterTable();
    });

    function filterTable() {
      const column = filterColumnSelect.value;
      const value = filterInput.value.toLowerCase();

      if (!column || !paginator) return;

      const allData = paginator ? paginator.setData.toString().includes('data') ? paginator.data || [] : [] : [];
      const filtered = allData.filter(item => {
        const cellVal = item[column];
        return cellVal && String(cellVal).toLowerCase().includes(value);
      });

      paginator.setData(filtered);
    }


    document.addEventListener('click', function(event) {
      if (event.target.classList.contains('time-in-btn') || event.target.classList.contains('time-out-btn')) {
        const button = event.target;
        const materialId = button.getAttribute('data-materialid');
        const item = JSON.parse(button.getAttribute('data-item').replace(/&apos;/g, "'"));
        const mode = button.getAttribute('data-mode');
        const itemId = button.getAttribute('data-itemid');
        const id = button.getAttribute('data-id');
        console.log(item)
        console.log('Material ID:', materialId);
        console.log('Mode:', mode);

        if (mode === 'timeIn') {
          // Fetch component stock information
          fetch('api/assembly/getSpecificComponent.php', {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json'
              },
              body: JSON.stringify({
                materialId
              })
            })
            .then(response => {
              if (!response.ok) throw new Error('Network response was not ok');
              return response.json();
            })
            .then(data => {
              const totalQty = item.total_quantity;
              let blockDueToStock = false;
              let criticalItems = [];
              let warningItems = [];
              let insufficientItems = [];
              let normalItems = [];

              data.forEach(component => {
                const {
                  actual_inventory,
                  critical,
                  minimum,
                  reorder,
                  normal,
                  components_name
                } = component;

                if (actual_inventory < totalQty) {
                  insufficientItems.push(component);
                  blockDueToStock = true;
                } else if (actual_inventory >= normal || actual_inventory >= minimum) {
                  normalItems.push(component);
                } else if (actual_inventory <= critical) {
                  criticalItems.push(component);
                } else if (actual_inventory <= minimum || actual_inventory <= reorder) {
                  warningItems.push(component);
                }
              });

              if (insufficientItems.length > 0) {
                Swal.fire({
                  icon: 'error',
                  title: 'Cannot Proceed',
                  html: `The following components don't have enough stock:<br><ul style="text-align: left;">${
                  insufficientItems.map(i => `<li>${i.components_name}: ${i.actual_inventory} in stock</li>`).join('')
            }</ul>`
                });
                return;
              }

              if (normalItems.length > 0) {
                Swal.fire({
                  icon: 'success',
                  title: 'Material Stocks',
                  html: `The following components are all sufficiently stocked for (${item.material_no})</br>${item.material_description}.<br>Proceed?`,
                  showCancelButton: true,
                  confirmButtonText: 'Yes, Proceed',
                  cancelButtonText: 'Cancel'
                }).then(result => {
                  if (result.isConfirmed) {
                    openQRModal(materialId, item, mode);
                  }
                });
              } else if (criticalItems.length > 0 || warningItems.length > 0) {
                let htmlContent = '';

                if (criticalItems.length > 0) {
                  htmlContent += `<strong style="color: red;">Critical Level:</strong><ul style="text-align: left;">${
              criticalItems.map(i => `<li>${i.components_name}: ${i.actual_inventory} in stock</li>`).join('')
            }</ul>`;
                }

                if (warningItems.length > 0) {
                  htmlContent += `<strong style="color: orange;">Low Stock Warning:</strong><ul style="text-align: left;">${
              warningItems.map(i => `<li>${i.components_name}: ${i.actual_inventory} in stock</li>`).join('')
            }</ul>`;
                }

                Swal.fire({
                  icon: 'warning',
                  title: 'Stock Level Alert',
                  html: htmlContent + `<br>Proceed anyway?`,
                  showCancelButton: true,
                  confirmButtonText: 'Yes, Proceed',
                  cancelButtonText: 'Cancel'
                }).then(result => {
                  if (result.isConfirmed) {
                    openQRModal(materialId, item, mode);
                  }
                });
              }
            })
            .catch(console.error);
        } else if (mode === 'timeOut') {
          // Handle TIME OUT
          currentMaterialId = materialId;
          currentItem = item;
          currentMode = mode;
          timeout_id = id;

          quantityModal.show();
        }
      }
    });
    document.getElementById('quantityForm').addEventListener('submit', function(e) {
      e.preventDefault();
      console.log('currentItem', currentItem);
      console.log('assembly', assemblyData);

      const quantityInput = document.getElementById('quantityInput');
      const quantity = parseInt(quantityInput.value, 10);

      if (!quantity || quantity < 1) {
        quantityInput.classList.add('is-invalid');
        quantityInput.focus();
        return;
      }

      quantityInput.classList.remove('is-invalid');

      // 🧠 Check done_quantity sum for the current reference_no
      const currentRef = currentItem.reference_no;
      const relatedAssemblies = Array.isArray(assemblyData) ?
        assemblyData.filter(item => item.reference_no === currentRef) : [];

      const totalDone = relatedAssemblies.reduce((sum, record) => {
        const done = parseInt(record.done_quantity, 10);
        return sum + (isNaN(done) ? 0 : done);
      }, 0);

      // Get the MAX total_quantity from related assemblies (not just currentItem)
      const maxQuantity = relatedAssemblies.reduce((max, record) => {
        const total = parseInt(record.total_quantity, 10);
        return total > max ? total : max;
      }, 0);

      const totalIfSubmitted = totalDone + quantity;

      if (totalIfSubmitted > maxQuantity) {
        Swal.fire({
          icon: 'warning',
          title: 'Exceeded Quantity',
          html: `The total quantity being assembled for <b>Reference No: ${currentRef}</b> exceeds the allowed maximum.<br><br>
        <b>Total Already Done:</b> ${totalDone}<br>
        <b>Input:</b> ${quantity}<br>
        <b>Maximum Allowed:</b> ${maxQuantity}`,
        });
        quantityInput.classList.add('is-invalid');
        quantityInput.focus();
        return;
      }

      quantityInput.classList.remove('is-invalid');
      quantityModal.hide();

      // Proceed if valid
      openQRModal(currentMaterialId, currentItem, currentMode, quantity, assemblyData);
    });

    function openQRModal(materialId, item, mode, quantity, assemblyData) {
      console.log(item, assemblyData);

      const matchingData = Array.isArray(assemblyData) ?
        assemblyData.find(data => data.material_no === item.material_no) :
        null;

      const pending_quantity = matchingData?.pending_quantity || 0;
      const expectedPersonInCharge = matchingData?.person_incharge || '';
      console.log(pending_quantity, expectedPersonInCharge);

      scanQRCodeForUser({
        onSuccess: ({
          user_id,
          full_name
        }) => {
          // ✅ Only check person-in-charge during timeout
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
            id: item.id,
            itemID: item.id,
            model: item.model_name,
            reference_no: item.reference_no,
            material_no: item.material_no,
            material_description: item.material_description,
            shift: item.shift,
            lot_no: item.lot_no,
            total_qty: item.total_quantity,
            full_name: full_name,
            date_needed: item.date_needed,
            inputQty: quantity,
            pending_quantity: pending_quantity
          };

          if (mode === 'timeOut') {
            data.done_quantity = quantity;
          }

          const apiEndpoint = mode === 'timeOut' ?
            '/mes/api/assembly/timeoutOperator.php' :
            '/mes/api/assembly/timeinOperator.php';

          fetch(apiEndpoint, {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json'
              },
              body: JSON.stringify(data)
            })
            .then(res => res.json())
            .then(response => {
              if (response.success) {
                Swal.fire({
                  icon: 'success',
                  title: 'Success',
                  text: 'Your operation was successful!',
                  confirmButtonColor: '#3085d6'
                });
              } else {
                Swal.fire('Error', response.message, 'error');
              }
            })
            .catch(err => {
              console.error('Request failed', err);
              Swal.fire('Error', 'Something went wrong.', 'error');
            });
        },
        onCancel: () => {
          console.log("QR scan cancelled or modal closed");
        }
      });
    }




    enableTableSorting(".table");
  </script>