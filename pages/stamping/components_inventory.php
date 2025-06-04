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
  const normal = item.normal;
  const maximum = item.maximum_inventory;

  let statusLabel = '';
  let statusColor = '';
  let isGood = false;

  if (inventory <= critical && inventory < minimum) {
    statusLabel = "Critical";
    statusColor = "red";
    showRequestButton = true;
  } else if (inventory <= minimum && inventory > critical) {
    statusLabel = "Minimum";
    statusColor = "orange";
    showRequestButton = true;
  } else if (inventory <= reorder && inventory < normal) {
    statusLabel = "Reorder ";
    statusColor = "yellow";
    showRequestButton = true;
  } else if (inventory >= normal) {
    statusLabel = "Good";
    statusColor = "green";
    isGood = true;
  } else {
    statusLabel = "Ok";
    statusColor = "#28a745";
  }

  // To keep text readable on colored background, white text except for yellow where black is better
  const textColor = (statusColor === "yellow") ? "black" : "white";

 if (isGood) {
    stockText = `<span title="${statusLabel}" 
      style="background-color: ${statusColor}; color: ${textColor}; " class="btn btn-sm" >
      ${statusLabel}
    </span>`;
  } else {
stockText = `<button type="button" class="btn btn-sm" 
  style="background-color: ${statusColor}; color: ${textColor};" 
  title="${statusLabel}"
  onclick="showInventoryStatus('${statusLabel}', '${item.material_no}', '${item.components_name}','${item.usage_type}','${item.process_quantity}', ${item.rm_stocks || 0})">
  ${statusLabel}
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

function showInventoryStatus(statusLabel, materialNo, componentName,usage,process_quantity,rmStocks) {
  if (rmStocks > 0) {
    Swal.fire({
      icon: 'info',
      title: 'Raw Materials Stock Exists',
      text: `There is still raw materials stock available for ${componentName} (${materialNo}).`,
    });
    return; // Do not proceed with request
  }
  console.log(materialNo,componentName,statusLabel,process_quantity);
  Swal.fire({
    title: `Request raw materials for ${componentName} (${materialNo})?`,
    text: `Inventory status: ${statusLabel}`,
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: 'Yes, proceed',
    cancelButtonText: 'Cancel'
  }).then((result) => {
    if (result.isConfirmed) {

      
  const postData = {
    material_no: materialNo,
    component_name: componentName,
    usage:usage,
    process_quantity:process_quantity,
  };

  fetch('api/stamping/requestRawMaterial.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify(postData)
  })
    .then(response => {
      if (!response.ok) throw new Error(`HTTP error! Status: ${response.status}`);
      return response.json(); 
    })
    .then(data => {
      if(data.status==="success"){
           Swal.fire({
            icon: 'success',
            title: 'Request Sent',
            text: `Requested units of ${componentName}.`,
          })
      }else{
           Swal.fire({
            icon: 'error',
            title: 'Already Requested',
            text: `The ${componentName} request is already at RM Warehouse.`,
          })
      }
    })
    }
  });
}

</script>