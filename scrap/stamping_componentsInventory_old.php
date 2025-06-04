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

<table class="table table" style="table-layout: fixed; width: 100%;">
  <thead>
    <tr>
    
      <th style="width: 10%; text-align: center;">Material No</th>
      <th style="width: 18%; text-align: center;">Component Name</th>
      <th style="width: 8%; text-align: center;">Usage Type</th>
      <th style="width: 8%; text-align: center;">Quantity</th>
      <th style="width: 8%; text-align: center;">Raw Material Qty</th>
    <th style="width: 8%; text-align: center;">Stock Status</th>
    <th style="width: 8%; text-align: center;">Action</th>
    </tr>
  </thead>
  <tbody id="data-body"></tbody>
</table>


      
      </div>
    </div>
  </div>
</div>
<!-- Request Component Modal -->
<div class="modal fade" id="requestComponentModal" tabindex="-1" aria-labelledby="requestComponentModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="requestComponentModalLabel">Request Component</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="requestComponentForm">
          <div class="mb-3">
            <label class="form-label">Material No</label>
            <input type="text" class="form-control" id="request_material_no" readonly>
          </div>
          <div class="mb-3">
            <label class="form-label">Component Name</label>
            <input type="text" class="form-control" id="request_component_name" readonly>
          </div>
          <div class="mb-3">
            <label for="request_quantity" class="form-label">Quantity to Request</label>
            <input type="number" class="form-control" id="request_quantity" name="request_quantity" min="1" required>
          </div>
          <button type="submit" class="btn btn-primary w-100">Submit Request</button>
        </form>
      </div>
    </div>
  </div>
</div>
<!-- Add Stock Modal -->
<div class="modal fade" id="addStockModal" tabindex="-1" aria-labelledby="addStockModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addStockModalLabel">Add Stock</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="addStockForm">
          <div class="mb-3">
            <label class="form-label">Material No</label>
            <input type="text" class="form-control" id="add_material_no" readonly>
          </div>
          <div class="mb-3">
            <label class="form-label">Component Name</label>
            <input type="text" class="form-control" id="add_component_name" readonly>
          </div>
          <div class="mb-3">
            <label for="add_quantity" class="form-label">Quantity to Add</label>
            <input type="number" class="form-control" id="add_quantity" name="add_quantity" min="1" required>
            <div id="quantity-error" class="text-danger mt-1" style="font-size: 0.875em; display: none;"></div>
          </div>

          <button type="submit" class="btn btn-info w-100">Assign Task</button>
        </form>
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

<script>
const dataBody = document.getElementById('data-body');
fetch('api/stamping/getComponents.php')
  .then(response => response.json())
  .then(data => {
    console.log(data);
    componentsData = data;
    dataBody.innerHTML = '';

    data.sort((a, b) => {
      const aNeedsRequest = a.actual_inventory <= a.reorder;
      const bNeedsRequest = b.actual_inventory <= b.reorder;
      return (bNeedsRequest - aNeedsRequest); // true = 1, false = 0
    });

    data.forEach(item => {
      let stockText = '';
      let showRequestButton = false;

      const inventory = item.actual_inventory;
      const reorder = item.reorder;
      const critical = item.critical;
      const minimum = item.minimum;

      if (inventory <= critical) {
        stockText = `<span title="Critical" style="color: red; font-weight: bold;">Critical</span>`;
        showRequestButton = true;
      } else if (inventory <= minimum) {
        stockText = `<span title="Below Minimum" style="color: orange; font-weight: bold;">Minimum</span>`;
        showRequestButton = true;
      } else if (inventory <= reorder) {
        stockText = `<span title="Reorder Level" style="color: green; font-weight: bold;">Reorder</span>`;
        showRequestButton = true;
      } else {
        stockText = `<span title="Stock OK" style="color: #28a745; font-weight: bold;">Ok</span>`;
      }


      let actionContent = '';

      const status = item.status?.toLowerCase();
      const section = item.section?.toLowerCase();

      // ✅ Always show Add Stock if rm_stocks exists
   if (item.rm_stocks == null || Number(item.rm_stocks) === 0) {
  // rm_stocks is undefined, null, or exactly 0 → Request
  actionContent = `<button 
    class="btn btn-warning btn-sm request-btn" 
    data-material="${item.material_no}" 
    data-name="${item.components_name}">
      Request
  </button>`;
} else if (Number(item.rm_stocks) > 0) {
  // rm_stocks has a positive value → Add Stock
  actionContent = `<button 
    class="btn btn-info btn-sm add-stock-btn" 
    data-material="${item.material_no}" 
    data-name="${item.components_name}">
      Add Stock
  </button>`;
}



      const row = document.createElement('tr');
      row.innerHTML = `
        <td style="text-align: center;">${item.material_no || ''}</td>
        <td style="text-align: center;">${item.components_name || ''}</td>
        <td style="text-align: center;">${item.usage_type || ''}</td>
        <td style="text-align: center;">${inventory || 0}</td>
           <td style="text-align: center;">${item.rm_stocks || 0}</td>
        <td style="text-align: center;">${stockText}</td>
        <td style="text-align: center;">${actionContent}</td>
      `;
      dataBody.appendChild(row);
    });
  })

  .catch(error => {
    console.error('Error fetching component data:', error);
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: 'Failed to load inventory data.'
    });
  });
const requestModalEl = document.getElementById('requestComponentModal');
const requestModalInstance = new bootstrap.Modal(requestModalEl);
const addStock = document.getElementById('addStockModal');
  const addStockModal = new bootstrap.Modal(addStock);
  // Request
dataBody.addEventListener('click', function (e) {
  if (e.target.classList.contains('request-btn')) {
    const material = e.target.getAttribute('data-material');
    const name = e.target.getAttribute('data-name');

    // 🟡 Log the clicked row
    const row = e.target.closest('tr');
    console.log('Clicked Row:', row);

    // Optional: log row data cell-by-cell
    const cells = row.querySelectorAll('td');
    cells.forEach((cell, index) => {
      console.log(`Cell ${index}:`, cell.textContent.trim());
    });

    // Fill modal inputs
    document.getElementById('request_material_no').value = material;
    document.getElementById('request_component_name').value = name;
    document.getElementById('request_quantity').value = '';

    requestModalInstance.show();
  }
});

document.getElementById('requestComponentForm').addEventListener('submit', function (e) {
  e.preventDefault();

  const materialNo = document.getElementById('request_material_no').value;
  const componentName = document.getElementById('request_component_name').value;
  const quantity = document.getElementById('request_quantity').value;

  // Prepare data to send
  const postData = {
    material_no: materialNo,
    component_name: componentName,
    quantity: quantity
  };

  fetch('api/stamping/sendRequestRM.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'  // assuming your API accepts JSON
    },
    body: JSON.stringify(postData)
  })
  .then(response => {
    if (!response.ok) throw new Error(`HTTP error! Status: ${response.status}`);
    return response.json(); // or response.text() depending on your API response
  })
  .then(data => {
    // Handle success, e.g. show success message and hide modal
    Swal.fire({
      icon: 'success',
      title: 'Request Sent',
      text: `Requested ${quantity} units of ${componentName}.`,
    }).then(()=>{
      location.reload();
    });

    requestModalInstance.hide();

    // Optionally reset form if you want
    document.getElementById('requestComponentForm').reset();
  })
  .catch(error => {
    console.error('Error sending request:', error);
    Swal.fire({
      icon: 'error',
      title: 'Request Failed',
      text: 'There was an error sending your request. Please try again.',
    });
  });
});

let maxRequestQuantity = 0; // Global max quantity

document.addEventListener('click', function (event) {
  if (event.target.classList.contains('add-stock-btn')) {
    const material = event.target.getAttribute('data-material');
    const name = event.target.getAttribute('data-name');

    document.getElementById('add_material_no').value = material;
    document.getElementById('add_component_name').value = name;
    document.getElementById('add_quantity').value = '';
    document.getElementById('quantity-error').style.display = 'none';

    fetch('api/stamping/getRequest.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
        material_no: material,
        component_name: name
      })
    })
    .then(res => res.json())
    .then(res => {
      console.log(res);
      console.log(res.length);
      if (res.length > 0 && res[0].quantity) {
        maxRequestQuantity = parseInt(res[0].quantity);
        document.getElementById('add_quantity').placeholder = `Max: ${maxRequestQuantity}`;
      } else {
        maxRequestQuantity = 0;
        document.getElementById('quantity-error').textContent = 'No pending request found for this component.';
        document.getElementById('quantity-error').style.display = 'block';
      }
    });

    addStockModal.show();
  }
});

document.getElementById('addStockForm').addEventListener('submit', function (e) {
  e.preventDefault();

  const material_no = document.getElementById('add_material_no').value;
  const component_name = document.getElementById('add_component_name').value;
  const quantity = parseInt(document.getElementById('add_quantity').value);
  const errorEl = document.getElementById('quantity-error');
  errorEl.style.display = 'none';
  errorEl.textContent = '';
  console.log(quantity,maxRequestQuantity)
  if (quantity > maxRequestQuantity) {
    errorEl.textContent = `Quantity must not exceed ${maxRequestQuantity} as you've requested before.`;
    errorEl.style.display = 'block';
    return;
  }
  fetch('api/stamping/scheduleTask.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({
      material_no,
      component_name,
      quantity
    })
  })
  .then(res => res.json())
  .then(res => {
    if (res.status === 'success') {
     Swal.fire('Success', res.message, 'success').then(() => {
      addStockModal.hide();
      location.reload(); // Reload after user closes success alert
    });
    } else {
      errorEl.textContent = res.message || 'Error adding stock.';
      errorEl.style.display = 'block';
    }
  })
  .catch(() => {
    errorEl.textContent = 'Network or server error occurred.';
    errorEl.style.display = 'block';
  });



});
</script>


