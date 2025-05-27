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
  <h6 class="card-title mb-0">Components Inventory</h6>
  <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addComponentModal">
    <i class="mdi mdi-plus"></i> Add Component
  </button>
</div>

<table class="table table" style="table-layout: fixed; width: 100%;">
  <thead>
    <tr>
    
      <th style="width: 10%; text-align: center;">Material No</th>
      <th style="width: 18%; text-align: center;">Component Name</th>
      <th style="width: 8%; text-align: center;">Usage Type</th>
      <th style="width: 8%; text-align: center;">Actual Inventory</th>
    <th style="width: 8%; text-align: center;">Stock Status</th>
    </tr>
  </thead>
  <tbody id="data-body"></tbody>
</table>


      
      </div>
    </div>
  </div>
</div>
<!-- Add Component Modal -->
<div class="modal fade" id="addComponentModal" tabindex="-1" aria-labelledby="addComponentModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addComponentModalLabel">Add Component</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="addComponentForm">
        <!-- Material No Dropdown -->
        <div class="mb-3">
          <label for="material_no" class="form-label">Material No</label>
          <select class="form-select" id="material_no" name="material_no" required>
            <option value="" disabled selected>Select Material No</option>
          </select>
        </div>

        <!-- Material Description Dropdown -->
        <div class="mb-3">
          <label for="components_name" class="form-label">Material Description</label>
          <select class="form-select" id="components_name" name="components_name" required>
            <option value="" disabled selected>Select Material Description</option>
          </select>
        </div>

          <div class="mb-3">
            <label for="usage_type" class="form-label">Usage</label>
            <input type="number" class="form-control" id="usage_type" name="usage_type" min="1" required>
          </div>
          <div class="mb-3">
            <label for="actual_inventory" class="form-label">Actual Inventory</label>
            <input type="number" class="form-control" id="actual_inventory" name="actual_inventory" min="0" required>
          </div>
          <button type="submit" class="btn btn-primary w-100">Add Component</button>
        </form>
      </div>
    </div>
  </div>
</div>


<script>
  let materialData = [];
  let componentsData = [];

  const materialNoSelect = document.getElementById('material_no');
  const descriptionSelect = document.getElementById('components_name');
  const addComponentForm = document.getElementById('addComponentForm');
  const addComponentModal = document.getElementById('addComponentModal');
  const dataBody = document.getElementById('data-body');

  // Load raw materials
  fetch('api/stamping/getRawMaterials.php')
    .then(response => response.json())
    .then(data => {
      materialData = data;

      const uniqueMaterialNos = [...new Set(data.map(item => item.material_no))];
      uniqueMaterialNos.forEach(materialNo => {
        const option = document.createElement('option');
        option.value = materialNo;
        option.textContent = materialNo;
        materialNoSelect.appendChild(option);
      });

      materialNoSelect.addEventListener('change', function () {
        const selectedMaterialNo = this.value;
        descriptionSelect.innerHTML = '<option value="" disabled selected>Select Material Description</option>';

        const filteredDescriptions = data.filter(item => item.material_no === selectedMaterialNo);
        filteredDescriptions.forEach(item => {
          const option = document.createElement('option');
          option.value = item.material_description;
          option.textContent = item.material_description;
          descriptionSelect.appendChild(option);
        });
      });
    })
    .catch(error => console.error('Error loading raw materials:', error));
fetch('api/stamping/getComponents.php')
  .then(response => response.json())
  .then(data => {
    componentsData = data;
    dataBody.innerHTML = '';

    data.forEach(item => {
      let stockText = '';
      if (item.actual_inventory <= item.critical) {
        stockText = `<span title="Critical" style="color: red; font-weight: bold;">Critical</span>`;
      } else if (item.actual_inventory <= item.minimum) {
        stockText = `<span title="Below Minimum" style="color: orange; font-weight: bold;">Minimum</span>`;
      } else if (item.actual_inventory <= item.reorder) {
        stockText = `<span title="Reorder Level" style="color: green; font-weight: bold;">Reorder</span>`;
      } else {
        stockText = `<span title="Stock OK" style="color: #28a745; font-weight: bold;">Ok</span>`;
      }

      const row = document.createElement('tr');
      row.innerHTML = `
       
        <td style="text-align: center;">${item.material_no || ''}</td>
        <td style="text-align: center;">${item.components_name || ''}</td>
        <td style="text-align: center;">${item.usage_type || ''}</td>
        <td style="text-align: center;">${item.actual_inventory || 0}</td>
         <td style="text-align: center;">${stockText}</td>
      `;
      dataBody.appendChild(row);
    });
  });

  // Handle form submission
  addComponentForm.addEventListener('submit', function (e) {
    e.preventDefault();

    const material_no = materialNoSelect.value;
    const components_name = descriptionSelect.value;
    const usage_type = parseInt(document.getElementById('usage_type').value);
    const actual_inventory = parseInt(document.getElementById('actual_inventory').value);

    const existingComponent = componentsData.find(item =>
      item.material_no === material_no && item.components_name === components_name
    );

    function submitData(isUpdate = false) {
      const formData = {
        material_no,
        components_name,
        usage_type,
        actual_inventory,
        update: isUpdate
      };

      fetch('api/stamping/addComponentMaterial.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(formData)
      })
        .then(res => res.json())
        .then(res => {
          if (res.success) {
            Swal.fire({
              icon: 'success',
              title: isUpdate ? 'Updated' : 'Success',
              text: isUpdate ? 'Component updated successfully.' : 'Component added successfully.',
              timer: 2000,
              showConfirmButton: false,
            });

            addComponentForm.reset();

            let modalInstance = bootstrap.Modal.getInstance(addComponentModal);
            if (!modalInstance) {
              modalInstance = new bootstrap.Modal(addComponentModal);
            }
            modalInstance.hide();

            setTimeout(() => {
              location.reload(); // Or refresh your table via JS
            }, 2100);
          } else {
            Swal.fire({
              icon: 'error',
              title: 'Oops...',
              text: res.message || 'Insert/update failed.',
            });
          }
        })
        .catch(error => {
          Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'An error occurred. Please try again.',
          });
          console.error('Fetch error:', error);
        });
    }

    if (existingComponent) {
      Swal.fire({
        icon: 'warning',
        title: 'Component Exists',
        text: 'This material number and component name already exist. Do you want to update it?',
        showCancelButton: true,
        confirmButtonText: 'Yes, update it',
        cancelButtonText: 'Cancel',
      }).then(result => {
        if (result.isConfirmed) {
          submitData(true);
        }
      });
    } else {
      submitData(false);
    }
  });
</script>

