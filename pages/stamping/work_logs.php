<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/sweetalert2@11.js"></script>
<script src="https://unpkg.com/html5-qrcode"></script>


<div class="page-content">
  <nav class="page-breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="#">Pages</a></li>
      <li class="breadcrumb-item" aria-current="page">Stamping Manpower Section</li>
    </ol>
  </nav>

  <div class="row">
    <div class="col-md-12 grid-margin stretch-card">
      <div class="card">
        <div class="card-body">
     <div class="d-flex justify-content-between align-items-center mb-3">
  <h6 class="card-title mb-0">To-do List</h6>
</div>

<table class="table table" style="table-layout: fixed; width: 100%;">
  <thead>
    <tr>
        <th style="width: 5%; text-align: center;">Material No</th>
        <th style="width: 10%; text-align: center;">Material Description</th>
        <th style="width: 5%; text-align: center;">Total Quantity</th>
        <th style="width: 5%; text-align: center;">Quantity</th>
        <th style="width: 10%; text-align: center;">Person Incharge</th>
        <th style="width: 7%; text-align: center;">Time In</th>
        <th style="width: 7%; text-align: center;">Time Out</th>
        
    </tr>
  </thead>
  <tbody id="data-body"></tbody>
</table>


      
      </div>
    </div>
  </div>
</div>

<script>
let mode = null;
let selectedRowData = null;
let fullData = null;

fetch('api/stamping/getWorklogs.php')
  .then(response => response.json())
  .then(data => {
    console.log(data);
    fullData = data;

    // Step 1: Group by reference_no
    const grouped = {};
    data.forEach(item => {
      if (!grouped[item.reference_no]) grouped[item.reference_no] = [];
      grouped[item.reference_no].push(item);
    });

    // Step 2: Flatten grouped entries and sort by stage within each group
    const sorted = Object.values(grouped)
      .flatMap(group => group.sort((a, b) => (parseInt(a.stage || 0) - parseInt(b.stage || 0))));

    console.log(sorted); // sorted list

    const dataBody = document.getElementById('data-body');
    dataBody.innerHTML = ''; // Clear existing rows if any

    sorted.forEach(item => {
      const row = document.createElement('tr');
      const status = item.status?.toLowerCase();
      const statusCellContent = status ? status.toUpperCase() : '<i>None</i>';

 row.innerHTML = `
  <td style="text-align: center;">(${item.stage})${item.material_no || ''}</td>
  <td style="text-align: center;">${item.components_name || '<i>Null</i>'}</td>
  <td style="text-align: center;">${item.total_quantity || '<i>Null</i>'}</td>
  <td style="text-align: center;">${item.quantity || '<i>Null</i>'}</td>
  <td style="text-align: center;">${item.person_incharge || '<i>Null</i>'}</td>
  <td style="text-align: center;">${item.time_in || '<i>Null</i>'}</td>
  <td style="text-align: center;">${item.time_out || '<i>Null</i>'}</td>

`;


      dataBody.appendChild(row);
    });
  });
</script>
