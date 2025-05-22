
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

<table class="table table">
  <thead>
    <tr>
      <th>Material No</th>
      <th>Description</th>
      <th>Model</th>
      <th>Qty</th>
      <th>Supplement</th>
      <th>Total Qty</th>
      <th>Shift</th>
      <th>Lot</th>
      <th>Status</th>
      <th>Time In | Time out</th>
    
      
    </tr>
  </thead>
  <tbody id="data-body"></tbody>
</table>

      
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
document.addEventListener('DOMContentLoaded', function () {
    fetch('api/assembly/getDeliveryforms.php')
        .then(response => response.json())
        .then(data => {
            console.log('Server response:');
            console.log(data); // Recommended for debugging arrays of objects
const tbody = document.getElementById('data-body');
data.forEach(item => {
  const row = document.createElement('tr');
  row.innerHTML = `
    <td>${item.material_no}</td>
    <td>${item.material_description}</td>
    <td>${item.model_name}</td>
    <td>${item.quantity}</td>
    <td>${item.supplement_order ?? '0'}</td>
    <td>${item.total_quantity}</td>
    <td>${item.shift}</td>
    <td>${item.lot_no}</td>
     <td>${item.status.toUpperCase()}</td>
     <td><button>TIME IN</button></td>
  `;





  tbody.appendChild(row);
});
    
        })
        .catch(error => {
            console.error('Error fetching data:', error);
        });
});
</script>

