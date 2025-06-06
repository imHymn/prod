<div class="page-content">
  <nav class="page-breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="#">Pages</a></li>
      <li class="breadcrumb-item" aria-current="page">Assembly Work Management</li>
    </ol>
  </nav>

  <div class="row">
    <div class="col-md-12 grid-margin stretch-card">
      <div class="card">
        <div class="card-body">
          <h6 class="card-title">Work Logs</h6>
<div class="row mb-3">
  <div class="col-md-3">
    <select id="filter-column" class="form-select">
      <option value="" disabled selected>Select Column</option>
      <option value="material_no">Material No</option>
      <option value="material_description">Material Description</option>
      <option value="quantity">Quantity</option>
      <option value="time_in">Time In</option>
      <option value="time_out">Time Out</option>
      <option value="person_incharge">Person Incharge</option>
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
       <th style="width: 15%; text-align: center;">Material Description</th>
    <th style="width: 8%; text-align: center;">Quantity</th>
    
    <th style="width: 10%; text-align: center;">Time In</th>
    <th style="width: 10%; text-align: center;">Time Out</th>
    <th style="width: 10%; text-align: center;">Person Incharge</th>
  </tr>
</thead>

  <tbody id="data-body"></tbody>
</table>

      
      </div>
    </div>
  </div>
</div>

<script>
const tbody = document.getElementById('data-body');
const filterColumn = document.getElementById('filter-column');
const filterInput = document.getElementById('filter-input');

let allData = []; // will hold combined QC + Rework data

function renderTable(data) {
  tbody.innerHTML = '';
  data.forEach(item => {
    const row = document.createElement('tr');
    row.innerHTML = `
      <td style="text-align: center;">${item.material_no || ''}</td>
      <td style="text-align: center; overflow: hidden; text-overflow: ellipsis;">${item.material_description || ''}</td>
      <td style="text-align: center;">${item.quantity}</td>
      <td style="text-align: center;">${item.time_in || ''}</td>
      <td style="text-align: center;">${item.time_out || ''}</td>
      <td style="text-align: center;">${item.person_incharge || ''}</td>
    `;
    tbody.appendChild(row);
  });
}

function loadData() {
  tbody.innerHTML = '';
  allData = [];

  fetch('api/qc/getQCData.php')
    .then(res => res.json())
    .then(qcData => {
      // Normalize QC data
      qcData.forEach(item => {
        if (item.time_out === null) return;
        allData.push({
          material_no: item.material_no,
          material_description: item.material_description,
          quantity: `${item.done_quantity || 0}/${item.total_quantity || 0}`,
          time_in: item.time_in,
          time_out: item.time_out,
          person_incharge: item.person_incharge
        });
      });

      return fetch('api/qc/getReworkData.php');
    })
    .then(res => res.json())
    .then(reworkData => {
      // Normalize Rework data and append
      reworkData.forEach(item => {
        if (item.qc_timeout === null) return;
        allData.push({
          material_no: (item.material_no || '') + ' (REWORK)',
          material_description: item.material_description,
          quantity: `${item.good || 0}/${item.quantity || 0}`,
          time_in: item.qc_timein,
          time_out: item.qc_timeout,
          person_incharge: item.qc_person_incharge
        });
      });

      renderTable(allData);
    })
    .catch(console.error);
}

// Enable/disable filter input based on selected column
filterColumn.addEventListener('change', () => {
  if (filterColumn.value) {
    filterInput.disabled = false;
    filterInput.value = '';
    renderTable(allData); // reset to full data
  } else {
    filterInput.disabled = true;
    filterInput.value = '';
    renderTable(allData);
  }
});

// Filter on input change
filterInput.addEventListener('input', () => {
  const col = filterColumn.value;
  const val = filterInput.value.toLowerCase();

  if (!col) return;

  const filtered = allData.filter(item => {
    const cell = (item[col] || '').toString().toLowerCase();
    return cell.includes(val);
  });

  renderTable(filtered);
});

// Initial load
loadData();

</script>
