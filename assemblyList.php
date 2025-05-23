<script src="https://unpkg.com/html5-qrcode"></script>

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
          <h6 class="card-title">Delivery List</h6>

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
      <th style="width: 25%; text-align: center;">Handler Name</th>
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

<script src="assets/js/sweetalert2@11.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />
<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
<script>

</script>
<script>
let html5QrcodeScanner =null;
document.addEventListener('DOMContentLoaded', function () {
  let assemblyMap = new Map();

  // Step 1: Fetch assembly data
  fetch('api/assembly/getAssemblyData.php')
    .then(response => response.json())
    .then(assemblyData => {
      // Map assembly items by their ID for fast lookup

      console.log(assemblyData)
      assemblyData.forEach(entry => {
        assemblyMap.set(entry.id, entry);
      });

      // Step 2: Fetch delivery data
      return fetch('api/assembly/getDeliveryforms.php');
    })
    .then(response => response.json())
    .then(deliveryData => {
      const tbody = document.getElementById('data-body');

      deliveryData.forEach(item => {
        const assemblyEntry = assemblyMap.get(item.id);
        const isDone = assemblyEntry && assemblyEntry.time_out;

        const row = document.createElement('tr');
        row.innerHTML = `
          <td style="text-align: center;">${item.material_no}</td>
          <td style="text-align: center;">${item.model_name}</td>
          <td style="text-align: center;">${item.quantity}</td>
          <td style="text-align: center;">${item.supplement_order ?? '0'}</td>
          <td style="text-align: center;">${item.total_quantity}</td>
          <td style="text-align: center;">${item.shift}</td>
          <td style="text-align: center;">${item.lot_no}</td>
     <td style="text-align: center; color: ${
  isDone ? '#28a745' : (item.status.toLowerCase() === 'pending' ? '#ffc107' : 'inherit')
}">
  ${isDone ? 'DONE' : item.status.toUpperCase()}
</td>

<td style="text-align: center;">${item.handler_name || '<i>NONE</i>'}</td>
<td style="text-align: center;">
  ${isDone ? `
    <span 
      class="btn btn-sm" 
      style="background-color: #28a745; color: white; cursor: default; pointer-events: none;"
    >
      DONE
    </span>
  ` : `
    <button 
      class="btn btn-sm btn-primary time-in-btn" 
      data-id="${item.material_no}"
      data-item='${JSON.stringify(item).replace(/'/g, "&apos;")}'
      data-mode="${item.handler_name ? 'timeOut' : 'timeIn'}"
    >
      ${item.handler_name ? 'TIME OUT' : 'TIME IN'}
    </button>
  `}
</td>


        `;
        tbody.appendChild(row);
      });

      attachQRListeners(); // Optional: call your QR code button logic here
    })
    .catch(error => console.error('Error fetching data:', error));
});


document.addEventListener('click', function (event) {
  if (event.target.classList.contains('time-in-btn')) {
    const button = event.target;
    const materialId = button.getAttribute('data-id');
    const item = JSON.parse(button.getAttribute('data-item').replace(/&apos;/g, "'"));
    const mode = button.getAttribute('data-mode');

    openQRModal(materialId, item, mode);
  }
});
function openQRModal(materialId, item, mode) {
  console.log(item)
  console.log(mode)
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

          const data = {
            full_name: full_name,
            id: item.id,
            material_no: item.material_no,
            material_description:item.material_description,
            model: item.model_name,
            lot_no: item.lot_no,
            shift:item.shift,
            total_qty: item.total_quantity,
            quantity: item.quantity,
            supplement_order: item.supplement_order,
            handler_name: item.handler_name,
            date_needed: item.date_needed,
            mode: mode // Pass mode to server
          };

          let apiEndpoint = '/mes/api/assembly/timeinOperator.php';
        if (mode === 'timeOut') {
        apiEndpoint = '/mes/api/assembly/timeoutOperator.php';
        console.log('Using timeOut endpoint:', apiEndpoint);
      } else {
        console.log('Using default timeIn endpoint:', apiEndpoint);
      }
          console.log(apiEndpoint);
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


