<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/sweetalert2@11.js"></script>
<script src="https://unpkg.com/html5-qrcode"></script>


<div class="page-content">
  <nav class="page-breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="#">Pages</a></li>
      <li class="breadcrumb-item" aria-current="page">Stamping Manpower Section</li>
    </ol>
  </nav>

  <div class="row">
    <div class="col-md-12 grid-margin stretch-card">
      <div class="card">
        <div class="card-body">
     <div class="d-flex justify-content-between align-items-center mb-3">
  <h6 class="card-title mb-0">To-do List</h6>
</div>
<div class="row mb-3">
  <div class="col-md-3">
    <select id="filter-column" class="form-select">
      <option value="" disabled selected>Select Column to Filter</option>
      <option value="material_no">Material No</option>
      <option value="components_name">Material Description</option>
      <option value="stage_name">Process</option>
      <option value="total_quantity">Total Quantity</option>
      <option value="person_incharge">Person Incharge</option>
      <option value="time_in">Time In</option>
      <option value="time_out">Time Out</option>
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
     
        <th style="width: 5%; text-align: center;">Material No</th>
        <th style="width: 10%; text-align: center;">Material Description</th>
         <th style="width: 5%; text-align: center;">Process</th>
        <th style="width: 5%; text-align: center;">Total Quantity</th>
        <th style="width: 10%; text-align: center;">Person Incharge</th>
        <th style="width: 7%; text-align: center;">Time</th>
        <th style="width: 7%; text-align: center;">Action</th>
         <th style="width: 5%; text-align: center;">View</th>
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
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="quantityModalLabel">Enter Quantity</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <div class="mb-3">
          <label for="timeoutQuantity" class="form-label">Quantity to Process</label>
          <input type="number" class="form-control" id="timeoutQuantity" min="1" value="1">
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" id="confirmQuantityBtn">Confirm</button>
      </div>
    </div>
  </div>
</div>

<script>
  let mode = null;
let selectedRowData = null;
let fullData = null;

const filterColumnSelect = document.getElementById('filter-column');
const filterInput = document.getElementById('filter-input');
const dataBody = document.getElementById('data-body');

fetch('api/stamping/getMuffler_comps.php')
  .then(response => response.json())
  .then(data => {
    console.log(data);
    fullData = data;
    renderTable(fullData);
  })
  .catch(console.error);

function renderTable(data) {
  // Step 1: Group by reference_no
  const grouped = {};
  data.forEach(item => {
    if (!grouped[item.reference_no]) grouped[item.reference_no] = [];
    grouped[item.reference_no].push(item);
  });

  // Step 2: Flatten grouped entries and sort by stage within each group
  const sorted = Object.values(grouped)
    .flatMap(group => group.sort((a, b) => (parseInt(a.stage || 0) - parseInt(b.stage || 0))));

  dataBody.innerHTML = ''; // Clear existing rows if any

  sorted.forEach(item => {
    if(item.status === 'done'){return;}
    const row = document.createElement('tr');
    const status = item.status?.toLowerCase();
    const statusCellContent = status ? status.toUpperCase() : '<i>None</i>';

    const hasTimeIn = item.time_in !== null && item.time_in !== '';
    const hasTimeOut = item.time_out !== null && item.time_out !== '';

    const itemDataAttr = encodeURIComponent(JSON.stringify(item));

    let actionButton = '';
    if (hasTimeIn && hasTimeOut) {
      actionButton = `<span class="btn btn-sm btn-primary">Done</span>`;
    } else {
      actionButton = hasTimeIn
        ? `<button type="button" 
                  class="btn btn-sm btn-success time-action-btn" 
                  data-item="${itemDataAttr}"
                  data-mode="time-out">
              Time Out
            </button>`
        : `<button type="button" 
                  class="btn btn-sm btn-primary time-action-btn" 
                  data-item="${itemDataAttr}"
                  data-mode="time-in">
              Time In
            </button>`;
    }

    row.innerHTML = `
      <td style="text-align: center;">${item.material_no || ''}</td>
      <td style="text-align: center;">${item.components_name || '<i>Null</i>'}</td>
      <td style="text-align: center;">${item.stage_name || ''}</td>
      <td style="text-align: center;">${item.total_quantity || '<i>Null</i>'}</td>
      <td style="text-align: center;">${item.person_incharge || '<i>Null</i>'}</td>
      <td style="text-align: center;">
        ${item.time_in || '<i>Null</i>'} / ${item.time_out || '<i>Null</i>'}
      </td>
      <td style="text-align: center;">
        ${actionButton}
      </td>
      <td style="text-align: center;">
        <button onclick="viewStageStatus('${item.material_no}', '${item.components_name}')" style="font-size: 16px;" class="btn btn-sm" title="View Stages">
          🔍
        </button>
      </td>
    `;

    dataBody.appendChild(row);
  });
}

// Enable/disable filter input based on dropdown
filterColumnSelect.addEventListener('change', () => {
  filterInput.value = '';
  filterInput.disabled = !filterColumnSelect.value;
  filterInput.focus();
  applyFilter();
});

filterInput.addEventListener('input', applyFilter);

function applyFilter() {
  const column = filterColumnSelect.value;
  const filterValue = filterInput.value.trim().toLowerCase();

  if (!column) {
    renderTable(fullData);
    return;
  }

  const filtered = fullData.filter(item => {
    let val = item[column];
    if (val === null || val === undefined) return false;

    // For time filter, handle combined fields
    if (column === 'time_in' || column === 'time_out') {
      return String(val).toLowerCase().includes(filterValue);
    }

    return String(val).toLowerCase().includes(filterValue);
  });

  renderTable(filtered);
}


document.getElementById('data-body').addEventListener('click', (event) => {
  const btn = event.target.closest('.time-action-btn');
  if (!btn) return;

  const encodedItem = btn.getAttribute('data-item');
   mode = btn.getAttribute('data-mode');

  selectedRowData = JSON.parse(decodeURIComponent(encodedItem));
  console.log('Selected Row:', selectedRowData);

  if (mode === 'time-in') {
console.log(fullData);

 const stage = parseInt(selectedRowData.stage || 0);
  const material_no = selectedRowData.material_no;

  // Get all items for this material_no
  const relatedItems = fullData.filter(item => item.material_no === material_no);

  // Find max total_quantity across related items (assuming total_quantity is same or max applies)
  const maxTotalQuantity = Math.max(...relatedItems.map(i => i.total_quantity || 0));

  if (stage === 1) {
    // Sum the quantity of all stage 1 items
    const stage1Items = relatedItems.filter(item => parseInt(item.stage) === 1);
    const sumStage1Quantity = stage1Items.reduce((sum, item) => sum + (item.quantity || 0), 0);

    // If stage 1 quantity sum equals max total_quantity, skip "ongoing" check because stage 1 is completed
    if (sumStage1Quantity >= maxTotalQuantity) {
      console.log('Stage 1 completed, no need to check ongoing status.');
      openQRModal(mode);
      return;
    }
  }

  if (stage > 1) {
    const prevStage = stage - 1;

    const previousStageItem = relatedItems.find(item =>
      parseInt(item.stage) === prevStage &&
      item.status?.toLowerCase() === 'ongoing'
    );

    // Also check if previous stage is completed (sum quantity === maxTotalQuantity)
    const prevStageItems = relatedItems.filter(item => parseInt(item.stage) === prevStage);
    const sumPrevStageQuantity = prevStageItems.reduce((sum, item) => sum + (item.quantity || 0), 0);

    const prevStageCompleted = sumPrevStageQuantity >= maxTotalQuantity;

    if (!previousStageItem && !prevStageCompleted) {
      Swal.fire({
        icon: 'warning',
        title: `Cannot Time-In for Stage ${stage}`,
        text: `Stage ${prevStage} must be marked as "ongoing" or completed before proceeding.`,
      });
      return;
    }
  }

  console.log('Ready for QR Time-In with:', selectedRowData);
  openQRModal(mode);


    // const stage = parseInt(selectedRowData.stage || 0);
    // const material_no = selectedRowData.material_no;

    // if (stage > 1) {
    //   const prevStage = stage - 1;

    //   const previousStageItem = fullData.find(item =>
    //     item.material_no === material_no &&
    //     parseInt(item.stage) === prevStage &&
    //     item.status?.toLowerCase() === 'ongoing'
    //   );

    //   if (!previousStageItem) {
    //     Swal.fire({
    //       icon: 'warning',
    //       title: `Cannot Time-In for Stage ${stage}`,
    //       text: `Stage ${prevStage} must be marked as "ongoing" before proceeding.`,
    //     });
    //     return;
    //   }
    // }

    // console.log('Ready for QR Time-In with:', selectedRowData);
    // openQRModal(mode);

  } else if (mode === 'time-out') {
  const quantityModal = new bootstrap.Modal(document.getElementById('quantityModal'));
  document.getElementById('timeoutQuantity').value = selectedRowData.total_quantity || 1;

  const confirmBtn = document.getElementById('confirmQuantityBtn');
  confirmBtn.onclick = () => {
    const inputQuantity = parseInt(document.getElementById('timeoutQuantity').value, 10);

    if (!inputQuantity || inputQuantity <= 0) {
      Swal.fire({
        icon: 'warning',
        title: 'Invalid Quantity',
        text: 'Please enter a valid, positive quantity greater than 0.'
      });
      return;
    }

    const referenceNo = selectedRowData.reference_no;
    const totalQuantity = parseInt(selectedRowData.total_quantity, 10) || 0;
    const pendingQuantity = parseInt(selectedRowData.pending_quantity, 10) || 0;

    // Sum current quantity from fullData with the same reference_no
    const sumQuantity = fullData
      .filter(row => row.reference_no === referenceNo)
      .reduce((sum, row) => sum + (parseInt(row.quantity, 10) || 0), 0);

    // Check if input exceeds pending quantity
    if (inputQuantity > pendingQuantity) {
      Swal.fire({
        icon: 'error',
        title: 'Pending Quantity Limit Exceeded',
        html: `
          <p>You entered <strong>${inputQuantity}</strong> units.</p>
          <p>But only <strong>${pendingQuantity}</strong> units are pending for processing.</p>
          <p>Please adjust your quantity accordingly.</p>
        `
      });
      return;
    }

    // Check if input causes sum to exceed total quantity
    if (sumQuantity + inputQuantity > totalQuantity) {
      const remaining = totalQuantity - sumQuantity;
      Swal.fire({
        icon: 'error',
        title: 'Total Quantity Limit Exceeded',
        html: `
          <p>Reference #: <strong>${referenceNo}</strong></p>
          <p>Already processed: <strong>${sumQuantity}</strong> / ${totalQuantity}</p>
          <p>Your input of <strong>${inputQuantity}</strong> would exceed the total allowed.</p>
          <p>You can only process up to <strong>${remaining}</strong> more units.</p>
        `
      });
      return;
    }

    selectedRowData.inputQuantity = inputQuantity;

    quantityModal.hide();
    console.log('Ready for QR Time-Out with:', selectedRowData);
    openQRModal(mode);
  };

  quantityModal.show();
}

});



function viewStageStatus(materialNo, componentName) {
  fetch('api/stamping/fetchStageStatus.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ material_no: materialNo, components_name: componentName })
  })
  .then(response => response.json())
  .then(data => {
    if (data.status === 'success') {
      const stages = data.stages || [];
      const len = stages.length;

      let content = '<i>No stages found</i>';

      if (len > 0) {
        // Two-row grid for odd ≥ 5 or even ≥ 6
        if ((len >= 5 && len % 2 === 1) || (len >= 6 && len % 2 === 0)) {
          const midpoint = Math.ceil(len / 2);
          const firstRow = stages.slice(0, midpoint);
          const secondRow = stages.slice(midpoint);

          content = `
            <div style="display: flex; flex-direction: column; gap: 16px; padding: 10px;">
              <div style="display: flex; gap: 12px; justify-content: center;">
                ${firstRow.map(stage => renderStageBox(stage)).join('')}
              </div>
              <div style="display: flex; gap: 12px; justify-content: center;">
                ${secondRow.map(stage => renderStageBox(stage)).join('')}
              </div>
            </div>
          `;
        } else {
          // Default horizontal scroll
          content = `
            <div style="display: flex; gap: 16px; overflow-x: auto; padding: 10px;">
              ${stages.map(stage => renderStageBox(stage)).join('')}
            </div>
          `;
        }
      }

      Swal.fire({
        title: 'Stage Status',
        html: content,
        icon: 'info',
        width: '60%'
      });
    } else {
      Swal.fire('Error', data.message || 'Could not fetch stage data.', 'error');
    }
  })
  .catch(err => {
    console.error('Fetch error:', err);
    Swal.fire('Error', 'Something went wrong.', 'error');
  });

    function renderStageBox(stage) {
    console.log(stage)
    return `
      <div style="border: 1px solid #ccc; padding: 10px; min-width: 200px; border-radius: 8px; box-shadow: 1px 1px 5px rgba(0,0,0,0.1);">
        <b>Section:</b> ${stage.section}<br>
        <b>Stage Name:</b> ${stage.stage_name}<br>
        <b>Status:</b> <span style="color: ${stage.status === 'done' ? 'green' : 'orange'}">${stage.status}</span><br>
        <br>(${stage.stage})
      </div>
    `;
  }
}


let isProcessingScan = false;

function openQRModal(mode) {
  const modalElement = document.getElementById('qrModal');
  const modal = new bootstrap.Modal(modalElement);
  modal.show();

  const resultContainer = document.getElementById('qr-result');
  resultContainer.textContent = "Waiting for QR scan...";
  isProcessingScan = false;

  const qrReader = new Html5Qrcode("qr-reader");
  html5QrcodeScanner = qrReader;
  console.log(mode);
  qrReader.start(
    { facingMode: "environment" },
    { fps: 10, qrbox: 550 },
    (decodedText, decodedResult) => {
      if (isProcessingScan) return;
      isProcessingScan = true;

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
          const idMatch = decodedText.match(/ID:\s*([^\n]+)/);
          const nameMatch = decodedText.match(/Name:\s*(.+)/);

          const parsedId = idMatch ? idMatch[1].trim() : null;
          const parsedName = nameMatch ? nameMatch[1].trim() : null;

          if (!selectedRowData) {
            console.error("No selectedRowData available!");
            isProcessingScan = false;
            qrReader.resume();
            return;
          }

       const {
        material_no,
        material_description,
        id,
        pending_quantity,
        quantity,
        inputQuantity,
        stage,
        total_quantity,
        process_quantity
      } = selectedRowData;


          const postData = {
            id,
            material_no,
            material_description,
            pending_quantity,
            name: parsedName,
            quantity,
            inputQuantity,
            stage,
            process_quantity,
            total_quantity
          };

         console.log("Using mode:", postData);

          const endpoint = mode === 'time-in'
            ? 'api/stamping/postTimeInTask.php'
            : 'api/stamping/postTimeOutTask.php';

          fetch(endpoint, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
              id,
              material_no,
              material_description,
              userId: parsedId,
              name: parsedName,
              quantity,
              inputQuantity,
              pending_quantity,
              total_quantity
            })
          })
          .then(res => res.json())
          .then(response => {
            console.log(response)
            if (response.status === 'success') {
                console.log('Calling Swal success...');
              Swal.fire('Success', response.message || `${mode.replace('-', ' ')} recorded.`, 'success');
              qrReader.stop().then(() => {
                qrReader.clear();
                modal.hide();
              });
            } else {
              Swal.fire('Error', response.message || 'Something went wrong.', 'error');
              isProcessingScan = false;
              qrReader.resume();
              resultContainer.textContent = "Waiting for QR scan...";
            }
          })
          .catch(err => {
            console.error(err);
            Swal.fire('Error', 'Network error occurred.', 'error');
            isProcessingScan = false;
            qrReader.resume();
            resultContainer.textContent = "Waiting for QR scan...";
          });

        } else {
          isProcessingScan = false;
          qrReader.resume();
          resultContainer.textContent = "Waiting for QR scan...";
        }
      });
    },
    (errorMessage) => {
      // Optional: Handle scan errors
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
    isProcessingScan = false;
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
