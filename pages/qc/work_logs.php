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
tbody.innerHTML = ''; // Clear only once before all fetches

// Fetch and render Assembly Work Logs (Main QC Data)
fetch('api/qc/getQCData.php')
  .then(response => response.json())
  .then(data => {
    console.log('QC Data:', data);

    data.forEach(item => {
      const row = document.createElement('tr');

      row.innerHTML = `
        <td style="text-align: center;">${item.material_no || ''}</td>
        <td style="text-align: center; overflow: hidden; text-overflow: ellipsis;">${item.material_description || ''}</td>
        <td style="text-align: center;">${item.done_quantity}/${item.total_quantity}</td>
        <td style="text-align: center;">${item.time_in || ''}</td>
        <td style="text-align: center;">${item.time_out || ''}</td>
        <td style="text-align: center;">${item.person_incharge || ''}</td>
      `;
      tbody.appendChild(row);
    });

    // After QC data, fetch and append Rework data
    return fetch('api/qc/getReworkData.php');
  })
  .then(response => response.json())
  .then(reworkData => {
    console.log('Rework Data:', reworkData);

    reworkData.forEach(item => {
      const row = document.createElement('tr');
   
      row.innerHTML = `
        <td style="text-align: center;">${item.material_no || ''}<br/>(REWORK)</td>
        <td style="text-align: center; overflow: hidden; text-overflow: ellipsis;">${item.material_description || ''}</td>
        <td style="text-align: center;">${item.qc_pending_quantity}/${item.quantity}</td>
        <td style="text-align: center;">${item.qc_timein || ''}</td>
        <td style="text-align: center;">${item.qc_timeout || ''}</td>
        <td style="text-align: center;">${item.qc_person_incharge || ''}</td>
      `;
      tbody.appendChild(row);
    });
  })
  .catch(error => {
    console.error('Error loading data:', error);
  });
</script>
