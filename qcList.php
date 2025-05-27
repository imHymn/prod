
<?php
session_start();
$name = $_SESSION['name'] ?? null;
?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>


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
    <th style="width: 8%; text-align: center;">Material No</th>
    <th style="width: 5%; text-align: center;">Model</th>
    <th style="width: 8%; text-align: center;">Total Qty</th>
    <th style="width: 8%; text-align: center;">Shift</th>
    <th style="width: 5%; text-align: center;">Lot</th>
    <th style="width: 15%; text-align: center;">Person Incharge</th>
      <th style="width: 8%; text-align: center;">Section</th>
    <th style="width: 8%; text-align: center;">Status</th>
  </tr>
</thead>

<tbody id="data-body" style="word-wrap: break-word; white-space: normal;"></tbody>
</table>

      
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
// Show/hide Rework/Replace inputs based on Not Good qty


let selectedRowData = null;

document.getElementById('notGoodQty').addEventListener('input', function () {
  const notGoodQty = parseInt(this.value, 10);
  const followUpSection = document.getElementById('followUpSection');
  if(selectedRowData.rework === null && selectedRowData.replace===null){
    if (!isNaN(notGoodQty) && notGoodQty > 0) {
      followUpSection.style.display = 'block';
    } else {
      followUpSection.style.display = 'none';
      // Reset rework/replace values when hidden
    
    }
  }

});
fetch('api/assembly/getAssemblyData.php')
  .then(response => response.json())
  .then(data => {
    console.log(data);
    const tbody = document.getElementById('data-body');
    tbody.innerHTML = ''; // clear existing rows
    data.filter(item => item.prev_section === 'QC' && item.curr_section !== 'WAREHOUSE' && item.status_rework == undefined || item.status_qc=="pending")
    .forEach(item => {
    const row = document.createElement('tr');

    row.innerHTML = `
      <td style="text-align: center;">${item.material_no || ''}</td>
      <td style="text-align: center;">${item.model || ''}</td>
      <td style="text-align: center;">${item.total_qty || ''}</td>
      <td style="text-align: center;">${item.shift || ''}</td> 
      <td style="text-align: center;">${item.lot_no || ''}</td>
      <td style="text-align: center;">${item.person_incharge_qc || '<i>NONE</i>'}</td>
      <td style="text-align: center;">
      ${
      (item.curr_section === null
        ? item.prev_section
        : (item.prev_section === item.curr_section ? item.prev_section : item.curr_section))
      + ((item.rework_timein && item.rework_timeout) ? ' (REWORK)' : '')
      }
      </td>
      <td style="text-align: center;">
        <button     
          class="btn btn-sm ${
            (item.good == null && item.not_good == null)
              ? 'btn-warning'
              : (item.curr_section === null || item.prev_section === item.curr_section
                  ? 'btn-primary'
                  : 'btn-warning')
          }"
          style="pointer-events: ${
            item.curr_section === 'ASSEMBLY'
              ? 'none'
              : ((item.good == null && item.not_good == null)
                  ? 'auto'
                  : (item.curr_section === null || item.prev_section === item.curr_section
                      ? 'none'
                      : 'auto'))
          };"
          ${item.curr_section === 'ASSEMBLY' ? 'disabled' : ''}
        >
          ${
            (item.good == null && item.not_good == null)
              ? 'PENDING'
              : (item.curr_section === null || item.prev_section === item.curr_section
                  ? 'DONE'
                  : `PENDING`)
          }
        </button>
      </td>
  `;

if (
  item.status_qc?.trim().toLowerCase() === 'pending'
) {
  row.style.cursor = 'pointer';
  row.addEventListener('click', () => {
    console.log(true)
    selectedRowData = item;
    openInspectionModal(item.id, item.total_qty,selectedRowData);
  });
} else if (
  item.curr_section?.trim().toUpperCase() === 'ASSEMBLY' &&
  item.status_rework?.trim().toLowerCase() === 'pending'
) {
  // Disable interaction explicitly (even though no click is added)
  row.style.pointerEvents = 'none';
  row.style.opacity = 0.6; // Optional visual cue
}
  tbody.appendChild(row);
});


  })
  .catch(error => {
    console.error('Error loading data:', error);
  });
  function openInspectionModal(id, totalQty,selectedRowData) {
    console.log(selectedRowData)
    document.getElementById('totalQtyHidden').value = totalQty;
    document.getElementById('recordIdHidden').value = id;

    document.getElementById('inspectionForm').reset();
    document.getElementById('errorMsg').textContent = '';

    const modal = new bootstrap.Modal(document.getElementById('inspectionModal'));
    modal.show();
  }

function submitInspection() {
  const name = <?= json_encode($name) ?>;

  const goodQty = parseInt(document.getElementById('goodQty').value, 10);
  const notGoodQty = parseInt(document.getElementById('notGoodQty').value, 10);
  const totalQty = parseInt(document.getElementById('totalQtyHidden').value, 10);
  const recordId = document.getElementById('recordIdHidden').value;
  const errorMsg = document.getElementById('errorMsg');
  const followErr = document.getElementById('followUpErrorMsg');

  const rework = parseInt(document.getElementById('rework').value, 10);
  const replace = parseInt(document.getElementById('replace').value, 10);

  // Clear error messages
  errorMsg.textContent = '';
  followErr.textContent = '';

  // --- Validation ---
  if (isNaN(goodQty) || isNaN(notGoodQty)) {
    errorMsg.textContent = 'Please enter both quantities.';
    return;
  }

  // Scenario: Original inspection (no rework history)
  if (selectedRowData.rework === null && selectedRowData.replace === null) {
    if ((goodQty + notGoodQty) !== selectedRowData.total_qty) {
      errorMsg.textContent = `Total must equal ${selectedRowData.total_qty} as Total Quantity.`;
      return;
    }else{
        if (notGoodQty > 0 && (rework + replace) !== notGoodQty) {
        followErr.textContent = 'Sum of Rework and Replace must equal to Not Good quantity.';
        return;
      }
   
    }
  } else {
    // Scenario: This is a follow-up (rework or replacement exists)
    if ((goodQty + notGoodQty) === selectedRowData.not_good) {
      if (notGoodQty > 0) {
        errorMsg.textContent = `There must not be No Good anymore as it is already from rework.`;
        return;
      }
    } else {
      errorMsg.textContent = `Total must equal ${selectedRowData.not_good} as No Good Quantity.`;
      return;
    }
  }

  // Validation: Rework + Replace must equal Not Good
  if (notGoodQty > 0 && (rework + replace) !== notGoodQty) {
    followErr.textContent = 'Sum of Rework and Replace must equal to Not Good quantity.';
    return;
  }

  // Stop if there are any validation messages
  if (errorMsg.textContent !== '' || followErr.textContent !== '') {
    return;
  }

  // --- Create data object for submission ---
  const postData = {
    id: recordId,
    name: name,
    good: goodQty,
    not_good: notGoodQty,
    total_qty: totalQty,
    material_no: selectedRowData.material_no,
    material_description: selectedRowData.material_description,
    model: selectedRowData.model,
    shift: selectedRowData.shift,
    lot_no: selectedRowData.lot_no,
    person_incharge: selectedRowData.person_incharge,
    date_needed: selectedRowData.date_needed,
    rework: rework,
    replace: replace
  };

  console.log(postData);
  if(selectedRowData.rework==null){
    api ='api/qc/makeRating.php';
  }else{
    api='api/qc/reworkRating.php';
  }

  console.log(api);
  // --- Submit to API ---
  // fetch('api/qc/makeRating.php', {
  //   method: 'POST',
  //   headers: { 'Content-Type': 'application/json' },
  //   body: JSON.stringify(postData)
  // })
  // .then(response => response.json())
  // .then(result => {
  //   console.log(result);
  //   if (result.success === true) {
  //     let message = 'Your operation was successful!';
  //     if (notGoodQty > 0) {
  //       message += '<br><strong>Note:</strong> Not good items will be reworked and sent back to Assembly.';
  //     } else {
  //       message += ' The finished product is now ready to be pulled out from the warehouse.';
  //     }

  //     Swal.fire({
  //       icon: 'success',
  //       title: 'Success',
  //       html: message,
  //       confirmButtonColor: '#3085d6'
  //     }).then(() => {
  //       location.reload();
  //     });

  //     // Hide modal
  //     const modalElement = document.getElementById('inspectionModal');
  //     const modal = bootstrap.Modal.getInstance(modalElement) || new bootstrap.Modal(modalElement);
  //     modal.hide();
  //   } else {
  //     errorMsg.textContent = result.message || 'Submission failed.';
  //   }
  // })
  // .catch(error => {
  //   console.error('Submission error:', error);
  //   errorMsg.textContent = 'An error occurred while submitting.';
  // });
}
</script>
