<div class="page-content">
  <nav class="page-breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="#">Pages</a></li>
      <li class="breadcrumb-item" aria-current="page">Assembly Manpower Management</li>
    </ol>
  </nav>

  <div class="row">
    <div class="col-md-12 grid-margin stretch-card">
      <div class="card">
        <div class="card-body">
          <h6 class="card-title">Manpower Efficiency</h6>

<table class="table table" style="table-layout: fixed; width: 100%;">
<thead>
  <tr>
    <!-- <th style="width: 20%; text-align: center;">Material No</th>
    <th style="width: 20%; text-align: center;">Material Description</th> -->
    <th style="width: 20%; text-align: center;">Person Incharge</th>
    <th style="width: 8%; text-align: center;">Total Qty</th>
    <th style="width: 15%; text-align: center;">Time In</th>
    <th style="width: 15%; text-align: center;">Time Out</th>
    <th style="width: 15%; text-align: center;">Time per Unit (min)</th>

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
tbody.innerHTML = ''; // clear existing table rows

// First fetch QC data and render
fetch('api/qc/getQCData.php')
  .then(response => response.json())
  .then(qcData => {
    // Build map of max total_quantity per reference_no
    const maxQtyMap = {};
    qcData.forEach(item => {
      const ref = item.reference_no;
      const qty = parseInt(item.total_quantity) || 0;
      if (!maxQtyMap[ref] || qty > maxQtyMap[ref]) {
        maxQtyMap[ref] = qty;
      }
    });

    // Render QC data rows
    qcData.forEach(item => {
      if (item.time_out === null) return;

      const ref = item.reference_no;
      const maxTotalQty = maxQtyMap[ref] || 0;
      const finishedQty = parseInt(item.done_quantity) || 0;

      let timeIn = item.time_in ? new Date(item.time_in) : null;
      let timeOut = item.time_out ? new Date(item.time_out) : null;
      let timeWorkedMin = 0;
      let timePerUnitMin = 0;

      if (timeIn && timeOut && timeOut > timeIn && finishedQty > 0) {
        timeWorkedMin = (timeOut - timeIn) / (1000 * 60);
        timePerUnitMin = timeWorkedMin / finishedQty;
      }

      const row = document.createElement('tr');
      row.innerHTML = `
        <!--<td style="text-align: center;">${item.material_no || '<i>NONE</i>'}</td>
        <td style="text-align: center; overflow: hidden; text-overflow: ellipsis;">${item.material_description || '<i>NONE</i>'}</td>-->
        <td style="text-align: center;">${item.person_incharge || '<i>NONE</i>'}</td>
        <td style="text-align: center;">${finishedQty}/${maxTotalQty}</td>
        <td style="text-align: center;">${item.time_in || ''}</td>
        <td style="text-align: center;">${item.time_out || ''}</td>
        <td style="text-align: center;">${timePerUnitMin ? timePerUnitMin.toFixed(2) : '-'}</td>
      `;
      tbody.appendChild(row);
    });

    // After QC data, fetch and render Rework data
    return fetch('api/qc/getManpowerRework.php');
  })
  .then(response => response.json())
  .then(reworkData => {
    // Render rework data rows
    console.log(reworkData);
    reworkData.forEach(item => {
       // Parse time in/out
      let timeIn = item.qc_timein ? new Date(item.qc_timein) : null;
      let timeOut = item.qc_timeout ? new Date(item.qc_timeout) : null;

      let timeWorkedMin = 0;
      let timePerUnitMin = 0;
      const quantity = parseInt(item.quantity) || 0;

      if (timeIn && timeOut && timeOut > timeIn && quantity > 0) {
        timeWorkedMin = (timeOut - timeIn) / (1000 * 60);
        timePerUnitMin = timeWorkedMin / quantity;
      }
      const row = document.createElement('tr');

      row.innerHTML = `
        <!--<td style="text-align: center;">${item.material_no || '<i>NONE</i>'}<br/>(REWORK)</td>
        <td style="text-align: center; overflow: hidden; text-overflow: ellipsis;">${item.material_description || '<i>NONE</i>'}</td>-->
        <td style="text-align: center;">${item.qc_person_incharge || '<i>NONE</i>'}<br/>(REWORK)</td>
        <td style="text-align: center;">${item.qc_pending_quantity || '-'}/${item.quantity || '-'}</td>
        <td style="text-align: center;">${item.qc_timein || ''}</td>
        <td style="text-align: center;">${item.qc_timeout || ''}</td>
        <td style="text-align: center;">${timePerUnitMin ? timePerUnitMin.toFixed(2) : '-'}</td>
      `;
      tbody.appendChild(row);
    });
  })
  .catch(error => {
    console.error('Error loading data:', error);
  });
</script>
