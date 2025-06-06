<script src="https://unpkg.com/html5-qrcode"></script>

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
          <h6 class="card-title">To-do List</h6>
<div class="row mb-3">
  <div class="col-md-3">
    <select id="filter-column" class="form-select">
      <option value="" disabled selected>Select Column to Filter</option>
      <option value="material_no">Material No</option>
      <option value="model_name">Model</option>
      <option value="total_quantity">Total Qty</option>
      <option value="shift">Shift</option>
      <option value="lot_no">Lot</option>
      <option value="person_incharge">Person Incharge</option>
      <option value="date_needed">Date needed</option>
    </select>
  </div>
  <div class="col-md-4">
    <input
      type="text"
      id="filter-input"
      class="form-control"
      placeholder="Type to filter..."
      disabled
    />
  </div>
</div>

<table class="table table" style="table-layout: fixed; width: 100%;">
  <thead>
    <tr>
      <th style="width: 15%; text-align: center;">Material No</th>
      <!-- <th style="width: 20%; text-align: center;">Description</th> -->
      <th style="width: 10%; text-align: center;">Model</th>

      <th style="width: 8%; text-align: center;">Total Qty</th>
      <th style="width: 8%; text-align: center; margin-right:-30px;">Shift</th>
      <th style="width: 8%; text-align: center;">Lot</th>
      <!-- <th style="width: 8%; text-align: center;">Status</th> -->
      <th style="width: 20%; text-align: center;">Person Incharge</th>
           <th style="width: 15%; text-align: center;">Date needed</th>
      <th style="width: 15%; text-align: center;">Time In | Time out</th>
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
      
      </div>
      <div class="modal-body">
        <div id="qr-reader" style="width: 100%"></div>
        <div id="qr-result" class="mt-3 text-center fw-bold text-success"></div>
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
          required
        />
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
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />
<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
<script>

</script>
<script>
let assemblyData = []; // Global variable
let currentMaterialId = null;
let currentItem = null;
let currentMode = null;
let timeout_id = null;
let quantityModal;

document.addEventListener('DOMContentLoaded', function () {
  const modalElement = document.getElementById('quantityModal');
  quantityModal = new bootstrap.Modal(modalElement);

  fetch('api/delivery/getDeliveryforms.php')
    .then(response => response.json())
    .then(deliveryData => {
      fetch('api/assembly/getTodoList.php')
        .then(response => response.json())
        .then(fetchedAssemblyData => {
          assemblyData = fetchedAssemblyData; // ✅ Store in global scope

          console.log('Assembly Data:', assemblyData);
          console.log('Delivery Data:', deliveryData);

          const tbody = document.getElementById('data-body');
          tbody.innerHTML = ''; 
          // ✅ Step 1: Filter to only include DELIVERY section
const filteredDeliveryData = deliveryData.filter(item => item.section === 'DELIVERY' || item.section === 'ASSEMBLY');

// ✅ Step 2: Prioritize and sort
filteredDeliveryData.sort((a, b) => {
  const aAssembly = assemblyData.find(x => String(x.itemID) === String(a.id));
  const bAssembly = assemblyData.find(x => String(x.itemID) === String(b.id));

  const aInProgress = aAssembly && aAssembly.time_in && !aAssembly.time_out ? 1 : 0;
  const bInProgress = bAssembly && bAssembly.time_in && !bAssembly.time_out ? 1 : 0;

  const aContinue = a.status.toLowerCase() === 'continue' ? 1 : 0;
  const bContinue = b.status.toLowerCase() === 'continue' ? 1 : 0;

  if (aInProgress !== bInProgress) return bInProgress - aInProgress;
  if (aContinue !== bContinue) return bContinue - aContinue;

  return a.reference_no.localeCompare(b.reference_no);
});

// ✅ Step 3: Render rows
filteredDeliveryData.forEach(item => {
  const assemblyRecord = assemblyData.find(a => String(a.itemID) === String(item.id));
  const personInCharge = assemblyRecord?.person_incharge || '<i>NONE</i>';

  let timeStatus = '';

  if (!assemblyRecord) {
    timeStatus = `<button 
                    class="btn btn-sm btn-primary time-in-btn"
                    data-item='${JSON.stringify(item).replace(/'/g, "&apos;")}'
                    data-materialid="${item.material_no}"
                    data-itemid="${item.id}"
                    data-mode="timeIn"
                  >TIME IN</button>`;
  } else if (!assemblyRecord.time_in) {
    timeStatus = `<button 
                    class="btn btn-sm btn-primary time-in-btn"
                    data-item='${JSON.stringify(item).replace(/'/g, "&apos;")}'
                    data-materialid="${item.material_no}"
                    data-itemid="${assemblyRecord.itemID}"
                    data-id="${assemblyRecord.id}"
                    data-mode="timeIn"
                  >TIME IN</button>`;
  } else if (assemblyRecord.time_in && !assemblyRecord.time_out) {
    const relatedAssemblyData = assemblyData.filter(a => a.reference_no === item.reference_no);
    timeStatus = `<button 
                    class="btn btn-sm btn-warning time-out-btn"
                    data-item='${JSON.stringify(item).replace(/'/g, "&apos;")}'
                    data-materialid="${item.material_no}"
                    data-itemid="${assemblyRecord.itemID}"
                    data-id="${assemblyRecord.id}"
                    data-mode="timeOut"
                    data-assemblyItem='${JSON.stringify(relatedAssemblyData).replace(/'/g, "&apos;")}'
                  >TIME OUT</button>`;
  } else {
    timeStatus = `<span class="btn btn-sm bg-success">DONE</span>`;
  }

  const row = document.createElement('tr');
  row.innerHTML = `
    <td style="text-align: center;">${item.material_no}</td>
    <td style="text-align: center;">${item.model_name}</td>
   <!--<td style="text-align: center;">
  ${item.total_quantity}${assemblyRecord?.pending_quantity != null ? `(${assemblyRecord.pending_quantity})` : ''}
</td>-->
  <td style="text-align: center;">${item.total_quantity} ${item.assembly_pending != null ? `(${item.assembly_pending})` : ''}</td>
    <td style="text-align: center;">${item.shift}</td>
    <td style="text-align: center;">${item.lot_no}</td>
    <!--<td style="text-align: center; color: ${item.status.toLowerCase() === 'pending' ? '#ffc107' : 'inherit'};">${item.status.toUpperCase()}</td>-->
    <td style="text-align: center;">${personInCharge}</td>
    <td style="text-align: center;">${item.date_needed}</td>
    <td style="text-align: center;">${timeStatus}</td>
  `;

  tbody.appendChild(row);
});

        });
    });
});
const filterColumnSelect = document.getElementById('filter-column');
const filterInput = document.getElementById('filter-input');
const tbody = document.getElementById('data-body');

filterColumnSelect.addEventListener('change', () => {
  filterInput.value = '';
  filterInput.disabled = !filterColumnSelect.value;
  filterInput.focus();
  filterTable(); // clear filter on change
});

filterInput.addEventListener('input', filterTable);

function filterTable() {
  const filterValue = filterInput.value.toLowerCase();
  const column = filterColumnSelect.value;
  if (!column) return;

  // Map your column keys to the <td> index in your table rows
  // You must keep these indexes in sync with your <th> order:
  // Material No (0), Model (1), Total Qty (2), Shift (3), Lot (4), Person Incharge (5), Date needed (6), Time In|Out (7)
  const columnIndexes = {
    material_no: 0,
    model_name: 1,
    total_quantity: 2,
    shift: 3,
    lot_no: 4,
    person_incharge: 5,
    date_needed: 6,
  };

  const index = columnIndexes[column];
  if (index === undefined) return;

  [...tbody.rows].forEach(row => {
    const cellText = row.cells[index].textContent.toLowerCase();
    row.style.display = cellText.includes(filterValue) ? '' : 'none';
  });
}

document.addEventListener('click', function (event) {
  if (event.target.classList.contains('time-in-btn') || event.target.classList.contains('time-out-btn')) {
    const button = event.target;
    const materialId = button.getAttribute('data-materialid');
    const item = JSON.parse(button.getAttribute('data-item').replace(/&apos;/g, "'"));
    const mode = button.getAttribute('data-mode');
    const itemId = button.getAttribute('data-itemid');
    const id = button.getAttribute('data-id');

    console.log('Material ID:', materialId);
    console.log('Mode:', mode);

    if (mode === 'timeIn') {
      // Fetch component stock information
      fetch('api/assembly/getSpecificComponent.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ materialId })
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
            html: `The following components are all sufficiently stocked.<br>Proceed?`,
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
  const relatedAssemblies = Array.isArray(assemblyData)
    ? assemblyData.filter(item => item.reference_no === currentRef)
    : [];

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









function openQRModal(materialId, item, mode,quantity,assemblyData) {
  console.log(item);
  const modalElement = document.getElementById('qrModal');
  const modal = new bootstrap.Modal(modalElement);
  modal.show();
const pending_quantity = assemblyData?.[0]?.pending_quantity || 0;
  const resultContainer = document.getElementById('qr-result');
  resultContainer.textContent = "Waiting for QR scan...";

  const qrReader = new Html5Qrcode("qr-reader");
  html5QrcodeScanner = qrReader;

  qrReader.start(
    { facingMode: "environment" },
    { fps: 10, qrbox: 550 },
    (decodedText, decodedResult) => {
      resultContainer.textContent = `QR Code Scanned: ${decodedText}`;
      qrReader.pause();

      Swal.fire({
        title: 'Confirm Scan',
        text: `Is this the correct QR code?\n${decodedText}`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, confirm',
        cancelButtonText: 'No, rescan'
      }).then((result) => {
        if (result.isConfirmed) {
          // Extract user_id and full_name from decodedText as before
          const idMatch = decodedText.match(/ID:\s*([^\n]+)/);
          const nameMatch = decodedText.match(/Name:\s*(.+)/);
          
          const user_id = idMatch ? idMatch[1].trim() : null;
          const full_name = nameMatch ? nameMatch[1].trim() : null;
        

          const data = {
            id: item.id,
            itemID:item.id,
            model:item.model_name,
            reference_no:item.reference_no,
            material_no: item.material_no,
            material_description:item.material_description,
            shift:item.shift,
            lot_no:item.lot_no,
            total_qty: item.total_quantity,
            full_name: full_name,
            date_needed:item.date_needed,
            inputQty:quantity,
              pending_quantity: pending_quantity
          };
      console.log(data);
          let apiEndpoint = '/mes/api/assembly/timeinOperator.php';
        if (mode === 'timeOut') {
        apiEndpoint = '/mes/api/assembly/timeoutOperator.php';
        data.done_quantity= quantity;
        }
   
          fetch(apiEndpoint, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
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
              })
              // .then(() => {
              //   location.reload();
              // });
            } else {
              Swal.fire('Error', response.message, 'error');
            }
          })
          .catch(err => {
            console.error('Request failed', err);
            Swal.fire('Error', 'Something went wrong.', 'error');
          });

          qrReader.stop().then(() => {
            qrReader.clear();
            modal.hide();
          }).catch(err => {
            console.error('Failed to stop scanner:', err);
          });
        } else {
          qrReader.resume();
          resultContainer.textContent = "Waiting for QR scan...";
        }
      });
    },
    (errorMessage) => {
      // handle scan errors
    }
  ).catch(err => {
    resultContainer.textContent = `Unable to start scanner: ${err}`;
  });

  modalElement.addEventListener('hidden.bs.modal', () => {
    if (html5QrcodeScanner) {
      html5QrcodeScanner.stop().then(() => {
        html5QrcodeScanner.clear();
      }).catch(err => {
        console.warn("QR scanner stop failed:", err);
      });
    }
  }, { once: true });
}

function stopQRScanner() {
    if (html5QrcodeScanner) {
        html5QrcodeScanner.stop().then(() => {
            html5QrcodeScanner.clear();
        }).catch(err => {
            console.warn("QR scanner stop failed:", err);
        });
    }
}
</script>


