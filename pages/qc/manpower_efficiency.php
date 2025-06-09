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
<div class="row mb-3">
  <div class="col-md-3">
    <select id="filter-column" class="form-select">
      <option value="" disabled selected>Select Column</option>
      <option value="person">Person Incharge</option>
      <option value="totalFinished">Quantity Finished</option>
      <option value="date">Date</option>
      <option value="timeIn">Time In</option>
      <option value="timeOut">Time Out</option>
      <option value="spent">Spent</option>
      <option value="standby">Standby</option>
      <option value="span">Total Span</option>
      <option value="timePerUnit">Time per Unit</option>
    </select>
  </div>
  <div class="col-md-4">
    <input type="text" id="filter-input" class="form-control" placeholder="Type to filter..." disabled />
  </div>
</div>

<table class="table table" style="table-layout: fixed; width: 100%;">
<thead>
  <tr>
    <th style="width: 20%; text-align: center;">Person Incharge</th>
    <th style="width: 10%; text-align: center;">Quantity</th>
    <th style="width: 10%; text-align: center;">Date</th>
    <th style="width: 10%; text-align: center;">Time In</th>
    <th style="width: 10%; text-align: center;">Time Out</th>
    <th style="width: 25%; text-align: center;">Spent/Standby/Total Span</th>
    
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
  function formatHoursMinutes(decimalHours) {
  const hours = Math.floor(decimalHours);
  const minutes = Math.round((decimalHours - hours) * 60);
  return `${hours} hrs${minutes > 0 ? ' ' + minutes + ' mins' : ''}`;
}

let mergedEntries = [];  // Will store processed entries for filtering

function renderTable(data) {
  const tbody = document.getElementById('data-body');
  tbody.innerHTML = '';

  data.forEach(entry => {
    const row = document.createElement('tr');
    row.innerHTML = `
      <td style="text-align: center;">${entry.person}</td>
      <td style="text-align: center;">${entry.totalFinished}/${entry.totalQty}</td>
      <td style="text-align: center;">${entry.date}</td>
      <td style="text-align: center;">${entry.timeIn}</td>
      <td style="text-align: center;">${entry.timeOut}</td>
      <td style="text-align: center;">
        ${entry.spent} / ${entry.standby} / ${entry.span}
      </td>
      <td style="text-align: center;">${entry.timePerUnit}</td>
    `;
    tbody.appendChild(row);
  });
}
function loadAndProcessData() {
  function extractDateOnly(datetimeStr) {
    return datetimeStr ? datetimeStr.slice(0, 10) : '';
  }

  Promise.all([
    fetch('api/qc/getQCData.php').then(res => res.json()),
    fetch('api/qc/getManpowerRework.php').then(res => res.json())
  ])
  .then(([qcData, reworkData]) => {
    const mergedData = {};
    const qcMaxQtyMap = {};
    const reworkMaxQtyMap = {};

    function addEntry(person, date, reference, timeIn, timeOut, finishedQty, totalQty, source) {
      const key = `${person}_${date}_${reference}`;
      if (!mergedData[key]) {
        mergedData[key] = {
          person,
          date,
          reference,
          totalFinished: 0,
          timeIns: [],
          timeOuts: [],
          totalWorkMinutes: 0
        };
      }
      const group = mergedData[key];

      if (source === 'qc') {
        if (!qcMaxQtyMap[key] || totalQty > qcMaxQtyMap[key]) qcMaxQtyMap[key] = totalQty;
      } else if (source === 'rework') {
        if (!reworkMaxQtyMap[key] || totalQty > reworkMaxQtyMap[key]) reworkMaxQtyMap[key] = totalQty;
      }

      if (timeIn && timeOut && timeOut > timeIn && finishedQty > 0) {
        const workedMin = (timeOut - timeIn) / (1000 * 60);
        group.totalWorkMinutes += workedMin;
        group.timeIns.push(timeIn);
        group.timeOuts.push(timeOut);
        group.totalFinished += finishedQty;
      }
    }

    // Process QC data
    qcData.forEach(item => {
      if (!item.time_in || !item.time_out || !item.person_incharge || !item.reference_no || !item.created_at) return;
      const finishedQty = parseInt(item.done_quantity) || 0;
      const totalQty = parseInt(item.total_quantity) || 0;
      const timeIn = new Date(item.time_in);
      const timeOut = new Date(item.time_out);
      const createdDate = extractDateOnly(item.created_at);
      addEntry(item.person_incharge, createdDate, item.reference_no, timeIn, timeOut, finishedQty, totalQty, 'qc');
    });

    // Process Rework data
    reworkData.forEach(item => {
      if (!item.qc_timein || !item.qc_timeout || !item.qc_person_incharge || !item.reference_no || !item.created_at) return;
      const finishedQty = parseInt(item.good) || 0;
      const totalQty = parseInt(item.quantity) || 0;
      const timeIn = new Date(item.qc_timein);
      const timeOut = new Date(item.qc_timeout);
      const createdDate = extractDateOnly(item.created_at);
      addEntry(item.qc_person_incharge, createdDate, item.reference_no, timeIn, timeOut, finishedQty, totalQty, 'rework');
    });

    // Prepare array with formatted data and extra fields for filtering
    mergedEntries = Object.values(mergedData).map(entry => {
      const firstIn = new Date(Math.min(...entry.timeIns.map(t => t.getTime())));
      const lastOut = new Date(Math.max(...entry.timeOuts.map(t => t.getTime())));
      const spanMinutes = (lastOut - firstIn) / (1000 * 60);
      const standbyMinutes = spanMinutes - entry.totalWorkMinutes;
      const timePerUnit = entry.totalFinished > 0 ? (entry.totalWorkMinutes / entry.totalFinished) : 0;
      const spanHours = spanMinutes / 60;
      const standbyHours = standbyMinutes / 60;
      const timePerUnitHours = timePerUnit / 60;
      const key = `${entry.person}_${entry.date}_${entry.reference}`;
      const totalQty = (qcMaxQtyMap[key] || 0) + (reworkMaxQtyMap[key] || 0);

      return {
        person: entry.person,
        date: entry.date,
        reference: entry.reference,
        totalFinished: entry.totalFinished,
        totalQty,
        timeIn: firstIn.toTimeString().slice(0,5),
        timeOut: lastOut.toTimeString().slice(0,5),
        spent: formatHoursMinutes(entry.totalWorkMinutes / 60),
        standby: formatHoursMinutes(standbyHours),
        span: formatHoursMinutes(spanHours),
        timePerUnit: timePerUnit > 0 ? formatHoursMinutes(timePerUnitHours) : '-'
      };
    });

    renderTable(mergedEntries);
  })
  .catch(console.error);
}


// Filtering logic
document.getElementById('filter-column').addEventListener('change', function() {
  const input = document.getElementById('filter-input');
  if (this.value) {
    input.disabled = false;
    input.value = '';
    renderTable(mergedEntries); // reset table on column change
  } else {
    input.disabled = true;
    input.value = '';
    renderTable(mergedEntries); // show all if no column selected
  }
});

document.getElementById('filter-input').addEventListener('input', function() {
  const col = document.getElementById('filter-column').value;
  const val = this.value.toLowerCase();

  if (!col) return;

  const filtered = mergedEntries.filter(entry => {
    const cellValue = (entry[col] || '').toString().toLowerCase();
    return cellValue.includes(val);
  });

  renderTable(filtered);
});

// Initial load
loadAndProcessData();

</script>
