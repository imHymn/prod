
<?php
session_start();
$role = $_SESSION['role'];
$production = $_SESSION['production'];
?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>

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
          <h6 class="card-title">Reworked Material</h6>

<table class="table table" style="table-layout: fixed; width: 100%;">
<thead>
  <tr>
    <th style="width: 15%; text-align: center;">Material No</th>
    <th style="width: 5%; text-align: center;">Model</th>
    <th style="width: 8%; text-align: center;">Shift</th>
    <th style="width: 8%; text-align: center;">Lot</th>
    <th style="width: 8%; text-align: center;">Total Qty</th>
    <th style="width: 15%; text-align: center;">Person Incharge</th>
    <th style="width: 15%; text-align: center;">Time In | Time out</th>
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
     let url='';
    let selectedRowData = null;
    let inspectionModal = null;
document.addEventListener('DOMContentLoaded', function () {
  fetch('api/qc/getRework.php')
    .then(response => response.json())
    .then(data => {
        console.log(data);
      const tbody = document.getElementById('data-body');
      tbody.innerHTML = '';

      // Sort items: prioritize those needing TIME OUT
      data.sort((a, b) => {
        const weight = item => {
          if (item.qc_timein && !item.qc_timeout) return 2; // Needs TIME OUT
          if (!item.qc_timein) return 1; // Needs TIME IN
          return 0; // Done
        };
        return weight(b) - weight(a); // Higher weight first
      });

      data.forEach(item => {
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
          <td style="text-align: center;">${item.material_no}</td>
          <td style="text-align: center;">${item.model}</td>
          <td style="text-align: center;">${item.shift}</td>
          <td style="text-align: center;">${item.lot_no}</td>
         <td style="text-align: center;">
  ${item.quantity}${item.qc_quantity? ` (${item.qc_quantity})` : ''}
</td>

          <td style="text-align: center;">${item.qc_person_incharge || '-'}</td>
          <td style="text-align: center;">${actionHtml}</td>
        `;

        tbody.appendChild(tr);
      });
    })
    .catch(error => {
      console.error('Fetch error:', error);
    });
});

document.addEventListener('click', function (event) {
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
    console.log(selectedRowData,mode);
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
                id: selectedRowData.id,
                full_name: full_name,
                inputQty:selectedRowData.inputQty,
                no_good:selectedRowData.no_good,
                good:selectedRowData.good,
                reference_no:selectedRowData.reference_no,
                quantity:selectedRowData.quantity,
                qc_pending_quantity:selectedRowData.qc_pending_quantity
            };

            console.log(data,mode);
             let url = '/mes/api/qc/timein_reworkOperator.php';
            if (mode === 'timeOut') {
            url = '/mes/api/qc/timeout_reworkOperator.php';

            };

          fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
            })
            .then(res => res.json())
            .then(response => {
            console.log(response); // 🔍 You’ll now see the response
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
