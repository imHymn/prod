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
  <div class="row mb-3">
    <div class="col-md-3">
      <select id="filter-column" class="form-select">
        <option value="" disabled selected>Select Column to Filter</option>
        <option value="material_no">Material No</option>
        <option value="components_name">Material Description</option>
        <option value="total_quantity">Total Quantity</option>
        <option value="quantity">Quantity</option>
        <option value="person_incharge">Person Incharge</option>
        <option value="time_in">Time In</option>
        <option value="time_out">Time Out</option>
      </select>
    </div>
    <div class="col-md-4">
      <input type="text" id="filter-input" class="form-control" placeholder="Type to filter..." disabled />
    </div>
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
let fullData = [];

const dataBody = document.getElementById('data-body');
const filterColumn = document.getElementById('filter-column');
const filterInput = document.getElementById('filter-input');

fetch('api/stamping/getWorklogs.php')
  .then(response => response.json())
  .then(data => {
    fullData = data;

    // Group by reference_no and sort by stage
    const grouped = {};
    data.forEach(item => {
      if (!grouped[item.reference_no]) grouped[item.reference_no] = [];
      grouped[item.reference_no].push(item);
    });

    const sorted = Object.values(grouped)
      .flatMap(group => group.sort((a, b) => (parseInt(a.stage || 0) - parseInt(b.stage || 0))));

    renderTable(sorted);
  });

function renderTable(data) {
  dataBody.innerHTML = '';

  data.forEach(item => {
    const row = document.createElement('tr');
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
}

// Enable/disable filter input based on column selection
filterColumn.addEventListener('change', () => {
  filterInput.value = '';
  filterInput.disabled = !filterColumn.value;
  renderTable(fullData);
});

// Live filter on input
filterInput.addEventListener('input', () => {
  const column = filterColumn.value;
  const keyword = filterInput.value.toLowerCase().trim();

  if (!column || keyword === '') {
    renderTable(fullData);
    return;
  }

  const filtered = fullData.filter(item => {
    const field = item[column];
    return field?.toString().toLowerCase().includes(keyword);
  });

  renderTable(filtered);
});
</script>