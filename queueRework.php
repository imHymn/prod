
<?php
session_start();
$name = $_SESSION['name'] ?? null;
  $section = $_SESSION['section'];
  
?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>

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
          <h6 class="card-title">QC List</h6>

<table class="table table" style="table-layout: fixed; width: 100%;">
<thead>
  <tr>
    <th style="width: 15%; text-align: center;">Material No</th>
    <th style="width: 5%; text-align: center;">Model</th>
     <th style="width: 8%; text-align: center;">Good</th>
      <th style="width: 8%; text-align: center;">Not Good</th>
    <th style="width: 8%; text-align: center;">Total Qty</th>
    <th style="width: 8%; text-align: center;">Shift</th>
    <th style="width: 8%; text-align: center;">Lot</th>
    <th style="width: 15%; text-align: center;">Person Incharge</th>

      <th style="width: 8%; text-align: center;">Section</th>
    <th style="width: 8%; text-align: center;">State</th>
    <th style="width: 8%; text-align: center;">Status</th>
  </tr>
</thead>

<tbody id="data-body" style="word-wrap: break-word; white-space: normal;"></tbody>
</table>

      
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
            <label for="rework" class="form-label">Rework</label>
            <input type="number" class="form-control" id="rework" required>
          </div>
          <div class="mb-3">
            <label for="replace" class="form-label">Replace</label>
            <input type="number" class="form-control" id="replace" required>
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
  let itemMap = {};

let selectedRowData = null;

   console.log(name)
fetch('api/assembly/getAssemblyData.php')
  .then(response => response.json())
  .then(data => {
    console.log(data);
    const tbody = document.getElementById('data-body');
    tbody.innerHTML = ''; // clear existing rows
data.filter(item => item.not_good >= 0&& item.status_rework != undefined && item.curr_section !== 'WAREHOUSE') // added condition here
  .forEach(item => {
  const row = document.createElement('tr');
    console.log(item.status_rework, typeof item.status_rework);
itemMap[item.id] = item;

  // Row content
  row.innerHTML = `
    <td style="text-align: center;">${item.material_no || ''}</td>
    <td style="text-align: center;">${item.model || ''}</td>
     <td style="text-align: center;">${item.good || ''}</td>
      <td style="text-align: center;">${item.not_good || ''}</td>
    <td style="text-align: center;">${item.total_qty || ''}</td>
    <td style="text-align: center;">${item.shift || ''}</td> 
    <td style="text-align: center;">${item.lot_no || ''}</td>
    <td style="text-align: center;">${item.rework_incharge || '<i>NONE</i>'}</td>

   <td style="text-align: center;">
  ${
    item.curr_section === null
      ? item.prev_section
      : (item.prev_section === item.curr_section ? item.prev_section : item.curr_section)
  }
</td>

<td style="text-align: center;">
  <span class="fw-bold ${
    (item.good == null && item.not_good == null)
      ? 'text-warning'
      : (item.rework_timein && item.rework_timeout
          ? 'text-success'
          : (item.curr_section === null || item.prev_section === item.curr_section
              ? 'text-success'
              : 'text-warning'))
  }">
    ${
      (item.good == null && item.not_good == null)
        ? 'PENDING'
        : (item.rework_timein && item.rework_timeout
            ? 'DONE'
            : (item.curr_section === null || item.prev_section === item.curr_section
                ? 'DONE'
                : 'PENDING'))
    }
  </span>
</td>
<td style="text-align: center;">
  <button 
    class="btn btn-sm btn-primary time-in-btn"
    data-id="${item.id}"
    data-item='${JSON.stringify(item).replace(/'/g, "&apos;")}'
    data-mode="${
      !item.rework_timein
        ? 'timeIn'
        : (item.rework_timein && !item.rework_timeout ? 'timeOut' : '')
    }"
    ${
      (<?= json_encode($section) ?> !== 'ADMINISTRATOR' || (item.rework_timein && item.rework_timeout))
        ? 'disabled'
        : ''
    }
  >
    ${
      !item.rework_timein
        ? 'TIME IN'
        : (item.rework_timein && !item.rework_timeout
            ? 'TIME OUT'
            : 'DONE')
    }
  </button>
</td>



  `;
      if (
        item.status_qc === 'pending' || 
        (item.curr_section === 'ASSEMBLY' && item.status_rework === 'pending')
      ) {
        row.style.cursor = 'pointer';
        row.addEventListener('click', () => {
          selectedRowData = item;

        
        });
      }
  tbody.appendChild(row);
});
  })
  .catch(error => {
    console.error('Error loading data:', error);
  });



document.addEventListener('click', function (event) {
   if (event.target.classList.contains('time-in-btn')) {
    const button = event.target;
    
    const item = JSON.parse(button.getAttribute('data-item').replace(/&apos;/g, "'"));
    const mode = button.getAttribute('data-mode');
    console.log(mode)
        console.log(item)
         var material_no= item.material_no;
             const materialId = material_no;
if(mode==='timeIn'){
 
  fetch('api/assembly/getComponentRework.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({ material_no })
    })
    .then(response => {
      if (!response.ok) {
        throw new Error('Network response was not ok');
      }
      return response.json();
    })
    .then(data => {
  
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
          reoder,
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
 } else if (mode === 'timeOut') {
  window.pendingTimeOutData = item; // store item globally

  // Set values in the modal inputs
  document.getElementById('rework').value = item.rework || 0;
  document.getElementById('replace').value = item.replace || 0;

  // Optionally update hidden inputs if needed
  document.getElementById('totalQtyHidden').value = item.total_qty || 0;
  document.getElementById('recordIdHidden').value = item.id || '';

  // Show the modal
  const inspectionModal = new bootstrap.Modal(document.getElementById('inspectionModal'));
  inspectionModal.show();
}

}
    
});
function submitInspection() {
  const rework = parseInt(document.getElementById('rework').value, 10) || 0;
  const replace = parseInt(document.getElementById('replace').value, 10) || 0;
  const item = window.pendingTimeOutData;

  const errorMsg = document.getElementById('errorMsg');

  // Validation: Rework + Replace must match not_good
  if ((rework + replace) !== item.not_good) {
    errorMsg.textContent = `Rework + Replace must equal ${item.not_good}.`;
    return;
  }

  // Clear any existing error message
  errorMsg.textContent = '';

  // Show confirmation alert before proceeding
  Swal.fire({
    title: 'Confirm Submission',
    html: `
      <p>Are you sure you want to submit the inspection data?</p>
      <strong>Rework:</strong> ${rework} <br>
      <strong>Replace:</strong> ${replace}
    `,
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: 'Yes, Submit',
    cancelButtonText: 'Cancel'
  }).then((result) => {
    if (result.isConfirmed) {
      // Proceed with submission
      window.pendingTimeOutData.rework = rework;
      window.pendingTimeOutData.replace = replace;

      // Close modal
      hideBootstrap4Modal('inspectionModal');

      // Proceed to open QR modal
      openQRModal(item.material_no, window.pendingTimeOutData, 'timeOut');
    }
  });
}


function hideBootstrap4Modal(modalId) {
  const modal = document.getElementById(modalId);
  if (!modal) return;

  // Remove the 'show' class that makes the modal visible
  modal.classList.remove('show');

  // Hide the modal by setting display to none
  modal.style.display = 'none';

  // Remove the modal-backdrop element manually
  const backdrop = document.querySelector('.modal-backdrop');
  if (backdrop) backdrop.parentNode.removeChild(backdrop);

  // Enable page scrolling again by removing modal-open from body
  document.body.classList.remove('modal-open');

  // Remove the aria-modal attribute (optional)
  modal.removeAttribute('aria-modal');
  modal.setAttribute('aria-hidden', 'true');
}



function openQRModal(materialId, item, mode) {
  console.log(item)
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
          
          console.log(materialId)
          const data = {
            full_name: full_name,
            id: item.id,
            good:item.good,
            not_good:item.not_good,
            material_no: item.material_no,
            material_description:item.material_description,
            model: item.model_name,
            lot_no: item.lot_no,
            shift:item.shift,
            total_qty: item.total_quantity,
            quantity: item.quantity,
            supplement_order: item.supplement_order,
            person_incharge_assembly: item.person_incharge_assembly,
            date_needed: item.date_needed,
              rework: item.rework,           // <-- Added
  replace: item.replace,  
            mode: mode // Pass mode to server
          };
          
          let apiEndpoint = '/mes/api/assembly/timein_reworkOperator.php';
        if (mode === 'timeOut') {
        apiEndpoint = '/mes/api/assembly/timeout_reworkOperator.php';
      } 
          console.log(data);
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
              }).then(() => {
                location.reload();
              });
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


</script>
