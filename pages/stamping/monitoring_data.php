<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
        <th style="width: 15%; text-align: center;">Material Description</th>
        <th style="width: 5%; text-align: center;">Quantity</th>
        <th style="width: 15%; text-align: center;">Person Incharge</th>
        
        <th style="width: 10%; text-align: center;">Time In</th>
        <th style="width: 10%; text-align: center;">Time Out</th>

    </tr>
  </thead>
  <tbody id="data-body"></tbody>
</table>


      
      </div>
    </div>
  </div>
</div>

<script>
fetch('api/stamping/getTask.php')
  .then(r => r.json())
  .then(data => {
    const tbody = document.getElementById('data-body');
    tbody.innerHTML = '';
    console.log(data)
    data.forEach(item => {
      // 1️⃣  Skip only what you really want to skip
      if (item.status?.toLowerCase() !== 'done' &&
          item.section?.toLowerCase() !== 'stamping') {
        return;
      }

      // 3️⃣  Render the row (note the seventh TD)
      const row = document.createElement('tr');
      row.innerHTML = `
        <td class="text-center">${item.material_no || ''}</td>
        <td class="text-center text-truncate" style="max-width:15ch">${item.material_description || '<i>Null</i>'}</td>
        <td class="text-center">${item.quantity || '<i>Null</i>'}</td>
        <td class="text-center">${item.person_incharge || '<i>Null</i>'}</td>
        <td class="text-center">${item.time_in || '<i>Null</i>'}</td>
        <td class="text-center">${item.time_out || '<i>Null</i>'}</td>
      `;
      tbody.appendChild(row);
    });
    // Handle button clicks

  });

</script>
