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

<table class="table table" style="table-layout: fixed; width: 100%;">
<thead>
  <tr>
    <th style="width: 15%; text-align: center;">Material No</th>
    <th style="width: 15%; text-align: center;">Model</th>
    <th style="width: 8%; text-align: center;">Total Qty</th>
    <th style="width: 8%; text-align: center;">Shift</th>
    <th style="width: 8%; text-align: center;">Lot</th>
    <th style="width: 8%; text-align: center;">Status</th>
    <th style="width: 25%; text-align: center;">Handler Name</th>
    <th style="width: 12%; text-align: center;">Time In</th>
    <th style="width: 12%; text-align: center;">Time Out</th>
  </tr>
</thead>

  <tbody id="data-body"></tbody>
</table>

      
      </div>
    </div>
  </div>
</div>

<script>
fetch('api/assembly/getAssemblyData.php')
  .then(response => response.json())
  .then(data => {
    console.log(data);
    const tbody = document.getElementById('data-body');
    tbody.innerHTML = ''; // clear existing rows

  data.forEach(item => {
  const isDone = item.status && item.status.toLowerCase() === 'done';
  const statusColor = isDone ? '#28a745' 
                    : (item.status && item.status.toLowerCase() === 'pending' ? '#ffc107' : 'inherit');
  const statusText = isDone ? 'DONE' : (item.status ? item.status.toUpperCase() : '');

  const row = document.createElement('tr');
  row.innerHTML = `
    <td style="text-align: center;">${item.material_no || ''}</td>
    <td style="text-align: center;">${item.model || ''}</td>
    <td style="text-align: center;">${item.total_qty || ''}</td>
    <td style="text-align: center;">${item.shift || ''}</td> 
    <td style="text-align: center;">${item.lot_no || ''}</td>
    <td style="text-align: center; color: ${statusColor};">${statusText}</td> 
    <td style="text-align: center;">${item.person_incharge || '<i>NONE</i>'}</td>
    <td style="text-align: center;">${item.time_in || ''}</td>
    <td style="text-align: center;">${item.time_out || ''}</td>
  `;
  tbody.appendChild(row);
});

  })
  .catch(error => {
    console.error('Error loading data:', error);
  });

</script>
