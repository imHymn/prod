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
      <option value="" disabled selected>Filter by column</option>
      <option value="person">Person Incharge</option>
      <option value="totalFinished">Quantity</option>
      <option value="date">Date</option>
      <option value="timeIn">Time In</option>
      <option value="timeOut">Time Out</option>
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

const filterColumn = document.getElementById('filter-column');
const filterInput = document.getElementById('filter-input');
const tbody = document.getElementById('data-body');

let mergedDataArray = []; // Array of objects for filtering and rendering

function extractDateOnly(datetimeStr) {
  return datetimeStr ? datetimeStr.slice(0, 10) : '';
}

function renderTable(data) {
  tbody.innerHTML = '';
  data.forEach(entry => {
    const firstIn = new Date(Math.min(...entry.timeIns.map(t => t.getTime())));
    const lastOut = new Date(Math.max(...entry.timeOuts.map(t => t.getTime())));

    const spanMinutes = (lastOut - firstIn) / (1000 * 60);
    const standbyMinutes = spanMinutes - entry.totalWorkMinutes;
    const timePerUnit = entry.totalFinished > 0 ? (entry.totalWorkMinutes / entry.totalFinished) : 0;

    const spanHours = spanMinutes / 60;
    const standbyHours = standbyMinutes / 60;
    const timePerUnitHours = timePerUnit / 60;

    const totalQty = (entry.assemblyMax || 0) + (entry.reworkMax || 0);

    const row = document.createElement('tr');
    row.innerHTML = `
      <td style="text-align: center;">${entry.person}</td>
      <td style="text-align: center;">${entry.totalFinished}/${totalQty}</td>
      <td style="text-align: center;">${entry.date}</td>
      <td style="text-align: center;">${firstIn.toTimeString().slice(0, 5)}</td>
      <td style="text-align: center;">${lastOut.toTimeString().slice(0, 5)}</td>
      <td style="text-align: center;">
        ${formatHoursMinutes(entry.totalWorkMinutes / 60)} / 
        ${formatHoursMinutes(standbyHours)} / 
        ${formatHoursMinutes(spanHours)}
      </td>
      <td style="text-align: center;">${timePerUnit > 0 ? formatHoursMinutes(timePerUnitHours) : '-'}</td>
    `;
    tbody.appendChild(row);
  });
}

function filterAndRender() {
  const col = filterColumn.value;
  const val = filterInput.value.toLowerCase();

  if (!col || !val) {
    renderTable(mergedDataArray);
    return;
  }

  const filtered = mergedDataArray.filter(entry => {
    let field = '';
    switch (col) {
      case 'person': field = entry.person; break;
      case 'totalFinished': field = `${entry.totalFinished}`; break;
      case 'date': field = entry.date; break;
      case 'timeIn': field = entry.timeIns.length ? entry.timeIns.map(d => d.toTimeString().slice(0,5)).join(', ') : ''; break;
      case 'timeOut': field = entry.timeOuts.length ? entry.timeOuts.map(d => d.toTimeString().slice(0,5)).join(', ') : ''; break;
      case 'timePerUnit': {
        const timePerUnit = entry.totalFinished > 0 ? (entry.totalWorkMinutes / entry.totalFinished) : 0;
        field = timePerUnit > 0 ? formatHoursMinutes(timePerUnit / 60) : '-';
        break;
      }
      default: field = '';
    }
    return field.toString().toLowerCase().includes(val);
  });

  renderTable(filtered);
}

Promise.all([
  fetch('api/assembly/getAssemblyData.php').then(res => res.json()),
  fetch('api/assembly/getManpowerRework.php').then(res => res.json())
])
.then(([assemblyData, reworkData]) => {
  const mergedData = {};
  const assemblyMaxMap = {};
  const reworkMaxMap = {};

  function addEntry(person, date, reference, timeIn, timeOut, finishedQty, totalQty, source = 'assembly') {
    const key = `${person}_${date}_${reference}`;
    if (!mergedData[key]) {
      mergedData[key] = {
        person,
        date,
        reference,
        totalFinished: 0,
        timeIns: [],
        timeOuts: [],
        totalWorkMinutes: 0,
        assemblyMax: 0,
        reworkMax: 0,
      };
    }

    const group = mergedData[key];
    const timeInDate = new Date(timeIn);
    const timeOutDate = new Date(timeOut);

    if (source === 'assembly') {
      if (!assemblyMaxMap[key] || totalQty > assemblyMaxMap[key]) {
        assemblyMaxMap[key] = totalQty;
      }
      group.assemblyMax = assemblyMaxMap[key];
    } else {
      if (!reworkMaxMap[key] || totalQty > reworkMaxMap[key]) {
        reworkMaxMap[key] = totalQty;
      }
      group.reworkMax = reworkMaxMap[key];
    }

    if (!isNaN(timeInDate) && !isNaN(timeOutDate) && timeOutDate > timeInDate && finishedQty > 0) {
      const workedMin = (timeOutDate - timeInDate) / (1000 * 60);
      group.totalWorkMinutes += workedMin;
      group.timeIns.push(timeInDate);
      group.timeOuts.push(timeOutDate);
      group.totalFinished += finishedQty;
    }
  }

  assemblyData.forEach(item => {
    if (!item.time_out || !item.time_in || !item.person_incharge || !item.reference_no || !item.created_at) return;

    const day = extractDateOnly(item.created_at);
    const finishedQty = parseInt(item.done_quantity) || 0;
    const totalQty = parseInt(item.total_quantity) || 0;

    addEntry(item.person_incharge, day, item.reference_no, item.time_in, item.time_out, finishedQty, totalQty, 'assembly');
  });

  reworkData.forEach(item => {
    if (!item.assembly_timeout || !item.assembly_timein || !item.assembly_person_incharge || !item.reference_no || !item.created_at) return;

    const day = extractDateOnly(item.created_at);
    const finishedQty = (parseInt(item.rework) || 0) + (parseInt(item.replace) || 0);
    const totalQty = parseInt(item.quantity) || 0;

    addEntry(item.assembly_person_incharge, day, item.reference_no, item.assembly_timein, item.assembly_timeout, finishedQty, totalQty, 'rework');
  });

  mergedDataArray = Object.values(mergedData);

  renderTable(mergedDataArray);
})
.catch(error => {
  console.error('Error loading data:', error);
});

// Enable/disable filter input based on column select
filterColumn.addEventListener('change', () => {
  if (filterColumn.value) {
    filterInput.disabled = false;
    filterInput.value = '';
    renderTable(mergedDataArray);
  } else {
    filterInput.disabled = true;
    filterInput.value = '';
    renderTable(mergedDataArray);
  }
});

filterInput.addEventListener('input', () => {
  filterAndRender();
});
</script>

