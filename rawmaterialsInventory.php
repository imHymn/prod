<!-- Bootstrap Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<div class="page-content">
  <nav class="page-breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="#">Pages</a></li>
      <li class="breadcrumb-item active" aria-current="page">Inventory</li>
    </ol>
  </nav>

  <div class="row">
    <div class="col-md-12 grid-margin stretch-card">
      <div class="card">
        <div class="card-body">
          <div class="d-flex justify-content-end mb-3">
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#materialModal">
              Add / Edit Material
            </button>
          </div>

          <h6 class="card-title">Raw Materials Inventory</h6>

          <table class="table" style="table-layout: fixed; width: 100%;">
            <thead>
              <tr>
                <th style="width: 8%; text-align: center;">Stock Status</th>
                <th style="width: 10%; text-align: center;">Material No</th>
                <th style="width: 18%; text-align: center;">Component Name</th>
                <th style="width: 18%; text-align: center;">Raw Material</th>
                <th style="width: 8%; text-align: center;">Usage Type</th>
                <th style="width: 8%; text-align: center;">Inventory</th>
                <th style="width: 8%; text-align: center;">Critical</th>
                <th style="width: 8%; text-align: center;">Minimum</th>
                <th style="width: 8%; text-align: center;">Normal</th>
              </tr>
            </thead>
            <tbody id="data-body"></tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal -->
<!-- Modal -->
<div class="modal fade" id="materialModal" tabindex="-1" aria-labelledby="materialModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="materialModalLabel">Raw Material Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <!-- Material No input instead of select -->
        <div class="mb-3">
          <label for="materialInput" class="form-label">Material No</label>
          <input type="text" class="form-control" id="materialInput" placeholder="Enter material number" />
        </div>

        <div class="mb-3">
          <label for="componentsNameInput" class="form-label">Component Name</label>
          <input type="text" class="form-control" id="componentsNameInput" placeholder="Enter component name" />
        </div>

        <div class="mb-3">
          <label for="rawMaterialName" class="form-label">Raw Material Name</label>
          <input type="text" class="form-control" id="rawMaterialName" placeholder="Enter raw material name" />
        </div>

        <div class="mb-3">
          <label for="actualInventory" class="form-label">Actual Inventory</label>
          <input type="number" class="form-control" id="actualInventory" placeholder="0" />
        </div>

        <div class="mb-3">
          <label for="usageType" class="form-label">Usage</label>
          <input type="text" class="form-control" id="usageType" placeholder="Usage Type" />
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-success" id="saveMaterialBtn">Save Changes</button>
      </div>
    </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
 let componentsData = [];
// Create modal instance once
const materialModalEl = document.getElementById('materialModal');
const materialModal = new bootstrap.Modal(materialModalEl);

fetch('api/stamping/getRawMaterials.php')
  .then(response => response.json())
  .then(data => {
    console.log(data);
    componentsData = data;

    // Populate inventory table as before
    const tbody = document.getElementById('data-body');
    tbody.innerHTML = '';

    data.forEach(item => {
      let stockIcon = '';
      if (item.actual_inventory <= item.critical) {
        stockIcon = `<span title="Critical" style="color: red;"><i class="mdi mdi-alert-circle"></i></span>`;
      } else if (item.actual_inventory <= item.minimum) {
        stockIcon = `<span title="Below Minimum" style="color: orange;"><i class="mdi mdi-alert"></i></span>`;
      } else if (item.actual_inventory <= item.reorder) {
        stockIcon = `<span title="Reorder Level" style="color: green;"><i class="mdi mdi-alert-outline"></i></span>`;
      } else {
        stockIcon = `<span title="Stock OK" style="color: #28a745;"><i class="mdi mdi-check-circle"></i></span>`;
      }

      const row = document.createElement('tr');
      row.innerHTML = `
        <td style="text-align: center;">${stockIcon}</td>
        <td style="text-align: center;">${item.material_no || ''}</td>
        <td style="text-align: center;">${item.material_description || ''}</td>
        <td style="text-align: center;">${item.raw_material || ''}</td>
        <td style="text-align: center;">${item.usage || ''}</td>
        <td style="text-align: center;">${item.actual_inventory || 0}</td>
        <td style="text-align: center;">${item.critical || 0}</td>
        <td style="text-align: center;">${item.minimum || 0}</td>
        <td style="text-align: center;">${item.normal || 0}</td>
        
      `;
      tbody.appendChild(row);
    });
  })
  .catch(error => {
    console.error('Error loading data:', error);
  });

// When user types in Material No or Component Name, auto-fill fields if found
function autofillFields() {
  const materialNo = document.getElementById('materialInput').value.trim();
  const componentName = document.getElementById('componentsNameInput').value.trim();

  if (!materialNo || !componentName) {
    document.getElementById('actualInventory').value = '';
    document.getElementById('usageType').value = '';
    return;
  }

  const selectedItem = componentsData.find(item =>
    item.material_no === materialNo && item.components_name === componentName
  );

  if (selectedItem) {
    document.getElementById('actualInventory').value = selectedItem.actual_inventory || 0;
    document.getElementById('usageType').value = selectedItem.usage_type || '';
    document.getElementById('rawMaterialName').value = selectedItem.raw_material_name || '';
  } else {
    document.getElementById('actualInventory').value = '';
    document.getElementById('usageType').value = '';
    document.getElementById('rawMaterialName').value = '';
  }
}

document.getElementById('materialInput').addEventListener('input', autofillFields);
document.getElementById('componentsNameInput').addEventListener('input', autofillFields);
document.getElementById('saveMaterialBtn').addEventListener('click', () => {
  const materialNo = document.getElementById('materialInput').value.trim();
  const componentName = document.getElementById('componentsNameInput').value.trim();
  const rawMaterialName = document.getElementById('rawMaterialName').value.trim();
  const actualInventory = document.getElementById('actualInventory').value;
  const usageType = document.getElementById('usageType').value;

  if (!materialNo || !componentName) {
    Swal.fire({
      icon: 'warning',
      title: 'Missing Fields',
      text: 'Please enter both Material No and Component Name.'
    });
    return;
  }

  const payload = {
    material_no: materialNo,
    components_name: componentName,
    raw_material_name: rawMaterialName,
    actual_inventory: actualInventory,
    usage_type: usageType
  };

  const existingEntry = componentsData.find(item =>
    item.material_no === materialNo &&
    item.material_description === componentName &&
    item.raw_material === rawMaterialName
  );

  const proceedWithSave = () => {
    fetch('api/stamping/addRawMaterials.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify(payload)
    })
      .then(response => {
        if (!response.ok) throw new Error('Network response was not ok');
        return response.json();
      })
      .then(result => {
  if (result.success) {
    Swal.fire({
      icon: 'success',
      title: 'Success!',
      text: existingEntry
        ? 'Material updated successfully.'
        : 'Material saved successfully.',
      timer: 2000,
      showConfirmButton: false
    }).then(() => {
      location.reload();
    });

    // Hide the modal using jQuery (Bootstrap 4+ compatible)
    $('#materialModal').modal('hide');
  } else {
    Swal.fire({
      icon: 'error',
      title: 'Oops...',
      text: result.message || 'Something went wrong!'
    });
  }
})

      .catch(error => {
        console.error('Error:', error);
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: error.message || 'Network error occurred.'
        });
      });
  };

  if (existingEntry) {
    Swal.fire({
      icon: 'info',
      title: 'Update Confirmation',
      text: 'This material already exists. Saving will update the existing entry.',
      showCancelButton: true,
      confirmButtonText: 'Yes, update it!',
      cancelButtonText: 'Cancel'
    }).then(result => {
      if (result.isConfirmed) {
        proceedWithSave();
      }
    });
  } else {
    proceedWithSave();
  }
});



</script>
