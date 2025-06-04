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
      <th style="width: 25%; text-align: center;">Person Incharge</th>
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
let html5QrcodeScanner =null;
let quantityModal; 
let currentMaterialId = null;
let currentItem = null;
let currentMode = null;
let timeout_id = null;
  let assemblyMap = new Map();
document.addEventListener('DOMContentLoaded', function () {

  const modalElement = document.getElementById('quantityModal');
  quantityModal = new bootstrap.Modal(modalElement);
  // Step 1: Fetch assembly data
  fetch('api/assembly/getAssemblyData.php')
    .then(response => response.json())
    .then(assemblyData => {
      // console.log(assemblyData);
      assemblyData.forEach(entry => {
        assemblyMap.set(entry.id, entry);
      });
      return fetch('api/assembly/getDeliveryforms.php');
    })
    .then(response => response.json())
    .then(deliveryData => {
      const tbody = document.getElementById('data-body');
 console.log(deliveryData);
      deliveryData.sort((a, b) => {
    // Put items with person_incharge_assembly (truthy) before those without
    if (a.person_incharge_assembly && !b.person_incharge_assembly) return -1;
    if (!a.person_incharge_assembly && b.person_incharge_assembly) return 1;
    return 0; // keep original order if both have or don't have
  }).forEach(item => {
     const assemblyEntry = assemblyMap.get(item.itemID);
let statusColor = '';
let statusLabel = '';

if (assemblyEntry && assemblyEntry.time_out) {
  statusColor = '#28a745';
  statusLabel = 'DONE';
} else if (item.person_incharge_assembly && item.section !== 'ASSEMBLY' && item.section !== 'DELIVERY') {
  statusColor = '#28a745';
  statusLabel = 'DONE';
} else if (item.status.toLowerCase() === 'pending') {
  statusColor = '#ffc107';
  statusLabel = 'PENDING';
} else {
  statusColor = 'inherit';
  statusLabel = item.status.toUpperCase();
}

// Determine what button to render based on rules
let buttonHtml = '';

if (assemblyEntry && assemblyEntry.time_out) {
  // CASE 1: There is already a TIME OUT in the assembly entry
  buttonHtml = `<span class="btn btn-sm" style="background-color: #28a745; color: white; cursor: default; pointer-events: none;">DONE</span>`;
} else if (item.person_incharge_assembly && item.section === 'ASSEMBLY') {
  // CASE 2: TIME OUT should be rendered
  buttonHtml = `
    <button 
      class="btn btn-sm btn-primary time-in-btn" 
      data-materialid="${item.material_no}"
      data-item='${JSON.stringify(item).replace(/'/g, "&apos;")}'
      data-mode="timeOut"
      data-itemid="${item.itemID}"
      data-id="${item.id}"
    >
      TIME OUT
    </button>
  `;
} else if (item.person_incharge_assembly && item.section !== 'ASSEMBLY' && item.section !== 'DELIVERY') {
  // CASE 3: Person is assigned, but it's no longer in ASSEMBLY/DELIVERY → consider it DONE
  buttonHtml = `<span class="btn btn-sm" style="background-color: #28a745; color: white; cursor: default; pointer-events: none;">DONE</span>`;
} else {
  // CASE 4: Default to TIME IN
  buttonHtml = `
    <button 
      class="btn btn-sm btn-primary time-in-btn" 
      data-materialid="${item.material_no}"
      data-item='${JSON.stringify(item).replace(/'/g, "&apos;")}'
      data-mode="timeIn"
      data-itemid="${item.itemID}"
      data-id="${item.id}"
    >
      TIME IN
    </button>
  `;
}


        const row = document.createElement('tr');
        row.innerHTML = `
          <td style="text-align: center;">${item.material_no}</td>
          <td style="text-align: center;">${item.model_name}</td>
          <td style="text-align: center;">${item.quantity}</td>
          <td style="text-align: center;">${item.supplement_order ?? '0'}</td>
          <td style="text-align: center;">${item.total_quantity}</td>
          <td style="text-align: center;">${item.shift}</td>
          <td style="text-align: center;">${item.lot_no}</td>
    <td style="text-align: center; color: ${statusColor};">
  ${statusLabel}
</td>

    <td style="text-align: center;">${item.person_incharge_assembly || '<i>NONE</i>'}</td>
    <td style="text-align: center;">
      ${buttonHtml}
    </td>


        `;
        tbody.appendChild(row);
      });

      
    })
    .catch(error => console.error('Error fetching data:', error));
});

document.addEventListener('click', function (event) {
  if (event.target.classList.contains('time-in-btn')) {

  
    const button = event.target;
    const materialId = button.getAttribute('data-materialid');
    const item = JSON.parse(button.getAttribute('data-item').replace(/&apos;/g, "'"));
    const mode = button.getAttribute('data-mode');

const itemId = button.getAttribute('data-itemid'); // This is now the correct entry.id
const id = button.getAttribute('data-id'); // This is now the correct entry.id

const assemblyEntry = assemblyMap.get(parseInt(itemId)); // Convert to integer if needed
// const assemblyEntry = assemblyMap.get(parseInt(itemId)); // Convert to integer if needed

console.log(id);


  console.log(mode)
if(mode==='timeIn'){
  console.log(item)
  fetch('api/assembly/getSpecificComponent.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({ materialId })
    })
    .then(response => {
      if (!response.ok) {
        throw new Error('Network response was not ok');
      }
      return response.json();
    })
    .then(data => {
      console.log(data)
      const totalQty = item.total_quantity;
      let blockDueToStock = false;
      let criticalItems = [];
      let warningItems = [];
      let insufficientItems = [];
      let normalItems=[];
      data.forEach(component => {
        const {
          actual_inventory,
          critical,
          minimum,
          reorder,
          normal,
          components_name
        } = component;
        console.log(normal)
        if (actual_inventory < totalQty) {
          insufficientItems.push(component);
          blockDueToStock = true;
        } else if (actual_inventory >= normal || actual_inventory >=minimum) {
          normalItems.push(component);
        }else if (actual_inventory <= critical) {
          criticalItems.push(component);
        } else if (actual_inventory <= minimum || actual_inventory <= reoder) {
          warningItems.push(component);
        }
      });

      // 🚫 Insufficient stock? Stop and show error
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
      if(normalItems.length>0){
           let htmlContent = '';
            Swal.fire({
          icon: 'success',
          title: 'Material Stocks',
          html: htmlContent + `The following components are all sufficiently stocked.<br>Proceed?`,
          showCancelButton: true,
          confirmButtonText: 'Yes, Proceed',
          cancelButtonText: 'Cancel'
        }).then(result => {
          if (result.isConfirmed) {
            openQRModal(materialId, item, mode);
          }
        });
      }
      // ⚠️ Display all low/critical in a single alert
      else if (criticalItems.length > 0 || warningItems.length > 0) {
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
        return;
      }


    })
    .catch(error => {
      console.error('There was a problem with the fetch operation:', error);
    });
  }else if(mode==='timeOut'){
 currentMaterialId = materialId;
  currentItem = item;
  currentMode = mode;
  timeout_id=id;
    quantityModal.show();
  // openQRModal(materialId, item, mode);
}
}
    
});
document.getElementById('quantityForm').addEventListener('submit', function(e) {
  e.preventDefault();

  const quantityInput = document.getElementById('quantityInput');
  const quantity = parseInt(quantityInput.value, 10);
  console.log(currentItem)
  if (!quantity || quantity < 1) {
    quantityInput.classList.add('is-invalid');
    quantityInput.focus();
    return;
  }
if(currentItem.assembly_quantity_pending === null){
  if (quantity > currentItem.total_quantity) {
      Swal.fire({
        icon: 'error',
        title: 'Invalid Quantity',
        text: `You cannot enter more than the total quantity (${currentItem.total_quantity}).`,
      });
      quantityInput.classList.add('is-invalid');
      quantityInput.focus();
      return;
    }
}else{
   if (quantity > currentItem.assembly_quantity_pending) {
      Swal.fire({
        icon: 'error',
        title: 'Invalid Quantity',
        text: `You cannot enter more than the Pending Quantity (${currentItem.assembly_quantity_pending}).`,
      });
      quantityInput.classList.add('is-invalid');
      quantityInput.focus();
      return;
    }
}
  

  quantityInput.classList.remove('is-invalid');

  quantityModal.hide();

  // Call openQRModal with all needed data
  openQRModal(currentMaterialId, currentItem, currentMode, quantity,timeout_id);
});


function openQRModal(materialId, item, mode,quantity,timeout_id) {

  const modalElement = document.getElementById('qrModal');
  const modal = new bootstrap.Modal(modalElement);
  modal.show();

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
          console.log(quantity);
          if(item.assembly_total_quantity !== null){
            if(quantity === item.assembly_quantity_pending){
              item.assembly_quantity_pending =0;
            }
          }
          const data = {
            full_name: full_name,
            itemID:timeout_id,
            id: item.id,
            material_no: item.material_no,
            material_description:item.material_description,
            model: item.model_name,
            lot_no: item.lot_no,
            shift:item.shift,
            total_qty: item.total_quantity,
            quantity: item.quantity,
            assembly_quantity_done:quantity,
            assembly_quantity_pending:item.assembly_quantity_pending,
            assembly_total_quantity:item.assembly_total_quantity,
            supplement_order: item.supplement_order,
            person_incharge_assembly: item.person_incharge_assembly,
            date_needed: item.date_needed,
            mode: mode,
            reference_no:item.reference_no
          };
      console.log(data);
          let apiEndpoint = '/mes/api/assembly/timeinOperator.php';
        if (mode === 'timeOut') {
        apiEndpoint = '/mes/api/assembly/timeoutOperator.php';
        console.log('Using timeOut endpoint:', apiEndpoint);
      } else {
        console.log('Using default timeIn endpoint:', apiEndpoint);
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


