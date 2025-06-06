<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="page-content">
  <nav class="page-breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="#">Pages</a></li>
      <li class="breadcrumb-item" aria-current="page">Inventory</li>
    </ol>
  </nav>

  <div class="row">
    <div class="col-md-12 grid-margin stretch-card">
      <div class="card">
        <div class="card-body">
     <div class="d-flex justify-content-between align-items-center mb-3">
  <h6 class="card-title mb-0">Request Orders</h6>
</div>
 <div class="row mb-3">
    <div class="col-md-3">
      <select id="filter-column" class="form-select">
        <option value="" disabled selected>Select Column to Filter</option>
        <option value="material_no">Material No</option>
        <option value="components_name">Component Name</option>
        <option value="actual_inventory">Quantity</option>
        <option value="status">Status</option>
      </select>
    </div>
    <div class="col-md-4">
      <input type="text" id="filter-input" class="form-control" placeholder="Type to filter..." disabled />
    </div>
  </div>
<table class="table table" style="table-layout: fixed; width: 100%;">
  <thead>
    <tr>
    
      <th style="width: 10%; text-align: center;">Material No</th>
      <th style="width: 18%; text-align: center;">Component Name</th>

      <th style="width: 8%; text-align: center;">Quantity</th>
<th style="width: 8%; text-align: center;">Status</th>


    <th style="width: 8%; text-align: center;">Action</th>
    </tr>
  </thead>
  <tbody id="data-body"></tbody>
</table>


      
      </div>
    </div>
  </div>
</div>
<script>


document.addEventListener('DOMContentLoaded', function () {
  fetch('api/rm/getIssued.php')
    .then(response => response.json())
    .then(issuedResponse => {
      console.log(issuedResponse);

      // Build a Set of pending issued items using material_no|component_name
      const pendingIssuedSet = new Set(
        issuedResponse.data
          .filter(entry => entry.status === 'pending')
          .map(entry => `${entry.material_no}|${entry.component_name}`)
      );

      // Fetch components inventory
      fetch('api/rm/getComponents.php')
        .then(response => response.json())
        .then(components => {
   
          const dataBody = document.getElementById('data-body');
          dataBody.innerHTML = ''; // Clear existing rows

          components.forEach(item => {
            const inventory = item.actual_inventory;
            const reorder = item.reorder;
            const critical = item.critical;
            const minimum = item.minimum;
            const normal = item.normal;
            const maximum = item.maximum_inventory;
           const uniqueKey = `${item.material_no}|${item.components_name}`;
            const alreadyIssued = pendingIssuedSet.has(uniqueKey);
            let statusLabel = '';
            let statusColor = '';
            let showRequestButton = false;
           
            if (inventory >= maximum) {
              statusLabel = "Maximum";
              statusColor = "green";
              showRequestButton = true;
            } else if (inventory <= critical && inventory < minimum) {
              statusLabel = "Critical";
              statusColor = "red";
              showRequestButton = true;

              // Auto-issue request if not already issued
              const uniqueKey = `${item.material_no}|${item.components_name}`;
              const alreadyIssued = pendingIssuedSet.has(uniqueKey);

              if (!alreadyIssued) {
                const calculatedQty = 300 * parseInt(item.usage_type || 0);
                if (calculatedQty > 0) {
                  sendIssueRequest({
                    id: item.id,
                    material_no: item.material_no,
                    component_name: item.components_name,
                    process_quantity: parseInt(item.process_quantity || 1),
                    quantity: calculatedQty,
                    stage_name:item.stage_name
                  });
                }
              }
            }
 else if (inventory <= minimum && inventory > critical) {
              statusLabel = "Minimum";
              statusColor = "orange";
              showRequestButton = true;
            } else if (inventory <= reorder && inventory < normal) {
              statusLabel = "Reorder";
              statusColor = "yellow";
              showRequestButton = true;
            } else if (inventory >= normal) {
              statusLabel = "Normal";
              statusColor = "green";
            } else {
              statusLabel = "Reorder";
              statusColor = "yellow";
              showRequestButton = true;
            }

            // Check if the item is already issued and pending
 

            const actionContent = alreadyIssued
             ? `<button class="btn btn-sm btn-secondary" disabled>Issued</button>`
            : (showRequestButton
              ? `<button class="btn btn-sm btn-primary send-request-btn" 
                  data-id="${item.id}" 
                  data-material="${item.material_no}" 
                  data-component="${item.components_name}" 
                  data-quantity="${item.actual_inventory}"
                  data-usage_type="${item.usage_type}"
                  data-process_quantity="${item.process_quantity}"
                  data-stage_name='${JSON.stringify(item.stage_name)}'>
                    Issue
                </button>`
              : `<span class="text-muted">-</span>`);

            const row = document.createElement('tr');
            row.innerHTML = `
              <td style="text-align: center;">${item.material_no || ''}</td>
              <td style="text-align: center;">${item.components_name || ''}</td>
              <td style="text-align: center;">${item.actual_inventory || 0}</td>
              <td style="text-align: center;">
                <span style="color: ${statusColor};">${statusLabel}</span>
              </td>
              <td style="text-align: center;">${actionContent}</td>
            `;
            dataBody.appendChild(row);
          })


                dataBody.addEventListener('click', function(e) {
          if (e.target.classList.contains('send-request-btn')) {
            const btn = e.target;

            // Get data attributes
            const id = btn.getAttribute('data-id');
            const materialNo = btn.getAttribute('data-material');
            const componentName = btn.getAttribute('data-component');
            const quantity = parseInt(btn.getAttribute('data-quantity')) || 0;
            const usageType = parseInt(btn.getAttribute('data-usage_type')) || 0;
            const process_quantity = parseInt(btn.getAttribute('data-process_quantity')) || 1;
            const stage_name = JSON.parse(btn.getAttribute('data-stage_name'));

            const calculatedQty = 300 * usageType;

            // Confirm default issue
            Swal.fire({
              title: 'Confirm Issue',
              html: `Are you sure you want to issue <strong>${calculatedQty}</strong> items for <strong>${componentName}</strong>?`,
              icon: 'warning',
              showCancelButton: true,
              confirmButtonText: 'Yes, Issue it',
              cancelButtonText: 'Change Quantity',
              confirmButtonColor: '#3085d6',
              cancelButtonColor: '#d33'
            }).then((result) => {
              if (result.isConfirmed) {
                // Proceed with default issue
            sendIssueRequest({
              id:id,
              material_no: materialNo,
              component_name: componentName,
              process_quantity: process_quantity,
              quantity: calculatedQty,
              stage_name:stage_name

            });

              } else if (result.dismiss === Swal.DismissReason.cancel) {
                // Show input modal for custom quantity
                Swal.fire({
                  title: 'Custom Quantity',
                  input: 'number',
                  inputLabel: `Enter quantity for ${componentName}:`,
                  inputAttributes: {
                    min: 1,
                    max: calculatedQty,
                    step: 1,
                  },
                  inputValue: calculatedQty,
                  showCancelButton: true,
                  confirmButtonText: 'Submit',
                  cancelButtonText: 'Cancel',
                  preConfirm: (value) => {
                    if (!value || parseInt(value) <= 0) {
                      Swal.showValidationMessage('Please enter a valid quantity');
                    }
                    return value;
                  }
                }).then((customResult) => {
                  if (customResult.isConfirmed) {
                    const userQty = parseInt(customResult.value);
                    sendIssueRequest({
                      id:id,
                    material_no: materialNo,
                    component_name: componentName,
                    process_quantity: process_quantity,
                    quantity: userQty,
                    stage_name:stage_name
                  });

                  }
                });
              }
            });
          }
        });
})
  .catch(error => {
    console.error('Error fetching data:', error);
  });
      });
  
    });
    const filterColumn = document.getElementById('filter-column');
const filterInput = document.getElementById('filter-input');

// Enable input only after selecting a column
filterColumn.addEventListener('change', function () {
  filterInput.disabled = false;
  filterInput.value = '';
  filterTable(); // Reset filter
});

// Filter table on input
filterInput.addEventListener('input', filterTable);

function filterTable() {
  const column = filterColumn.value;
  const query = filterInput.value.toLowerCase();
  const rows = document.querySelectorAll('#data-body tr');

  rows.forEach(row => {
    const cells = {
      material_no: row.cells[0]?.textContent.toLowerCase(),
      components_name: row.cells[1]?.textContent.toLowerCase(),
      actual_inventory: row.cells[2]?.textContent.toLowerCase(),
      status: row.cells[3]?.textContent.toLowerCase()
    };

    if (!column || !query) {
      row.style.display = '';
    } else if (cells[column].includes(query)) {
      row.style.display = '';
    } else {
      row.style.display = 'none';
    }
  });
}

  function sendIssueRequest(data) {

    console.log(data);
  fetch('api/rm/postIssuedComponent.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
     body: JSON.stringify(data)
  })
  .then(response => response.json())
  .then(result => {
    if (result.status='success') {
      Swal.fire('Success', 'Component issued successfully.', 'success');
    } else {
      Swal.fire('Error', result.message || 'Failed to issue component.', 'error');
    }
  })
  .catch(error => {
    console.error('Issue Request Error:', error);
    Swal.fire('Error', 'Something went wrong while issuing the component.', 'error');
  });
}

</script>
