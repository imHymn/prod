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
fetch('api/assembly/getAssemblyData.php')
  .then(response => response.json())
  .then(data => {
    console.log(data);
    const tbody = document.getElementById('data-body');
    tbody.innerHTML = ''; // Clear existing rows

    // Render assembly data
    data.forEach(item => {
      if (item.time_out === null) return;

      let timeIn = item.time_in ? new Date(item.time_in) : null;
      let timeOut = item.time_out ? new Date(item.time_out) : null;

      let timeWorkedMin = 0;
      let timePerUnitMin = 0;
      const finishedQty = parseInt(item.done_quantity) || 0;
      const totalQty = parseInt(item.total_quantity) || 0;

      if (timeIn && timeOut && timeOut > timeIn && finishedQty > 0) {
        timeWorkedMin = (timeOut - timeIn) / (1000 * 60);
        timePerUnitMin = timeWorkedMin / finishedQty;
      }

      const row = document.createElement('tr');
      row.innerHTML = `
       <td style="text-align: center;">${item.material_no}</td>
      <td style="text-align: center; overflow: hidden; text-overflow: ellipsis;">${item.material_description}</td>
        <td style="text-align: center;">${finishedQty}/${totalQty}</td>
        <td style="text-align: center;">${item.time_in || ''}</td>
        <td style="text-align: center;">${item.time_out || ''}</td>
         <td style="text-align: center;">${item.person_incharge || '<i>NONE</i>'}</td>
      `;
      tbody.appendChild(row);
    });

    // After assembly data, fetch and render rework data
    return fetch('api/assembly/getManpowerRework.php');
  })
  .then(response => response.json())
  .then(reworkData => {
    console.log(reworkData);
    const tbody = document.getElementById('data-body');

    // Render rework data rows
    reworkData.forEach(item => {
      let timeIn = item.assembly_timein ? new Date(item.assembly_timein) : null;
      let timeOut = item.assembly_timeout ? new Date(item.assembly_timeout) : null;

      let timeWorkedMin = 0;
      let timePerUnitMin = 0;
       const finishedQty = parseInt(item.rework) + parseInt(item.replace) ;
      const totalQty = parseInt(item.quantity) || 0;

      if (timeIn && timeOut && timeOut > timeIn && finishedQty > 0) {
        timeWorkedMin = (timeOut - timeIn) / (1000 * 60);
        timePerUnitMin = timeWorkedMin / finishedQty;
      }

      const row = document.createElement('tr');
      
      row.innerHTML = `
      <td style="text-align: center;">${item.material_no}<br/>(REWORK)</td>
      <td style="text-align: center; overflow: hidden; text-overflow: ellipsis;">${item.material_description}</td>
        <td style="text-align: center;">${finishedQty}/${totalQty}</td>
        <td style="text-align: center;">${item.assembly_timein || ''}</td>
        <td style="text-align: center;">${item.assembly_timeout || ''}</td>
          <td style="text-align: center;">${item.assembly_person_incharge || '<i>NONE</i>'}</td>
      `;
      tbody.appendChild(row);
    });
  })
  .catch(error => {
    console.error('Error loading data:', error);
  });
</script>
