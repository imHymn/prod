<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="page-content">
  <nav class="page-breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="#">Pages</a></li>
      <li class="breadcrumb-item" aria-current="page">Stamping Components Inventory</li>
    </ol>
  </nav>

  <div class="row">
    <div class="col-md-12 grid-margin stretch-card">
      <div class="card">
        <div class="card-body">
     <div class="d-flex justify-content-between align-items-center mb-3">
  <h6 class="card-title mb-0">Components Inventory</h6>

</div>
 <div class="row mb-3">
            <div class="col-md-3">
              <select id="filter-column" class="form-select">
                <option value="" disabled selected>Select Column to Filter</option>
                <option value="material_no">Material No</option>
                <option value="components_name">Component Name</option>
                <option value="usage_type">Usage Type</option>
                <option value="actual_inventory">Quantity</option>
                <option value="rm_stocks">Raw Material Qty</option>
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
    
      <th style="width: 10%; text-align: center;">Material No</th>
      <th style="width: 18%; text-align: center;">Component Name</th>
      <th style="width: 8%; text-align: center;">Usage Type</th>
      <th style="width: 8%; text-align: center;">Quantity</th>
      <th style="width: 8%; text-align: center;">Raw Material Qty</th>
    <th style="width: 8%; text-align: center;">Stock Status</th>
    </tr>
  </thead>
  <tbody id="data-body"></tbody>
</table>


      
      </div>
    </div>
  </div>
</div>

<script>
  const dataBody = document.getElementById('data-body');
  const filterColumn = document.getElementById('filter-column');
  const filterInput = document.getElementById('filter-input');
  let componentsData = [];

  fetch('api/stamping/getComponents.php')
    .then(response => response.json())
    .then(data => {
      console.log(data);
      componentsData = data;

      renderTable(componentsData);
    })
    .catch(error => {
      console.error('Error fetching component data:', error);
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: 'Failed to load inventory data.'
      });
    });

  function renderTable(data) {
    dataBody.innerHTML = '';

    data.sort((a, b) => {
      const aNeedsRequest = a.actual_inventory <= a.reorder;
      const bNeedsRequest = b.actual_inventory <= b.reorder;
      return (bNeedsRequest - aNeedsRequest); // true = 1, false = 0
    });

    data.forEach(item => {

      const inventory = item.actual_inventory;
      const reorder = item.reorder;
      const critical = item.critical;
      const minimum = item.minimum;
      const maximum = item.maximum_inventory;

      let statusLabel = '';
      let statusColor = '';
      let isGood = false;

      if (inventory <= critical) {
        statusLabel = "Critical";
        statusColor = "red";
      } else if (inventory <= minimum && inventory > critical) {
        statusLabel = "Minimum";
        statusColor = "orange";
      } else if (inventory <= reorder && inventory > minimum) {
        statusLabel = "Reorder";
        statusColor = "yellow";
      } else if (inventory > reorder && inventory <= maximum) {
        statusLabel = "Normal";
        statusColor = "green";
      } else if (inventory > maximum) {
        statusLabel = "Maximum";
        statusColor = "green";
      }

      const textColor = (statusColor === "yellow") ? "black" : "white";

      let stockText = '';
      if (isGood) {
        stockText = `<span title="${statusLabel}" 
          style="background-color: ${statusColor}; color: ${textColor}; " class="btn btn-sm" >
          ${statusLabel}
        </span>`;
      } else {
        stockText = `<button type="button" class="btn btn-sm" 
          style="background-color: ${statusColor}; color: ${textColor};" 
          title="${statusLabel}"
          >
          ${statusLabel}
        </button>`;
      }

      const row = document.createElement('tr');
      row.innerHTML = `
        <td style="text-align: center;">${item.material_no || ''}</td>
        <td style="text-align: center;">${item.components_name || ''}</td>
        <td style="text-align: center;">${item.usage_type || ''}</td>
        <td style="text-align: center;">${inventory || 0}</td>
        <td style="text-align: center;">${item.rm_stocks || 0}  ${item.rm_stocks ? '<br/>(Ongoing)' : ''}</td>
        <td style="text-align: center;">${stockText}</td>
      `;
      dataBody.appendChild(row);
    });
  }

  // Enable input only when a column is selected
  filterColumn.addEventListener('change', () => {
    filterInput.value = '';
    filterInput.disabled = !filterColumn.value;
    if (!filterColumn.value) {
      renderTable(componentsData);
    }
  });

  // Filter table by selected column and input text
  filterInput.addEventListener('input', () => {
    const column = filterColumn.value;
    const filterText = filterInput.value.trim().toLowerCase();

    if (!column) return; // no column selected

    if (!filterText) {
      renderTable(componentsData);
      return;
    }

    const filtered = componentsData.filter(item => {
      const value = item[column];

      if (value === null || value === undefined) return false;

      // Convert to string and check includes
      return value.toString().toLowerCase().includes(filterText);
    });

    renderTable(filtered);
  });
</script>