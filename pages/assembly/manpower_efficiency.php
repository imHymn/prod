<?php include './components/reusable/tablesorting.php'; ?>
<?php include './components/reusable/tablepagination.php'; ?>
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
   

             <div class="d-flex align-items-center justify-content-between mb-2">
        <h6 class="card-title">Manpower Efficiency</h6>
  <small id="last-updated" class="text-muted" style="font-size:13px;"></small>
</div>
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

<table class="table" style="table-layout: fixed; width: 100%;">
  <thead>
    <tr>
      <th style="width: 7%; text-align: center;">Date <span class="sort-icon"></span></th>
      <th style="width: 15%; text-align: center;">Person Incharge <span class="sort-icon"></span></th>
      <th style="width: 7%; text-align: center;">Quantity <span class="sort-icon"></span></th>
      <th style="width: 7%; text-align: center;">Time In <span class="sort-icon"></span></th>
      <th style="width: 7%; text-align: center;">Time Out <span class="sort-icon"></span></th>
      <th style="width: 15%; text-align: center;">Total Working Time <span class="sort-icon"></span></th>
      <th style="width: 10%; text-align: center;">Target Cycle Time <span class="sort-icon"></span></th>
      <th style="width: 10%; text-align: center;">MPEFF <span class="sort-icon"></span></th>
    </tr>
  </thead>
  <tbody id="data-body"></tbody>
</table>

<nav>
  <ul id="pagination" class="pagination justify-content-center"></ul>
</nav>

      
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

let mergedDataArray = [];
let filteredData = [];
let paginator;

function extractDateOnly(datetimeStr) {
  return datetimeStr ? datetimeStr.slice(0, 10) : '';
}
function renderPageCallback(pageData, cycleData) {
  tbody.innerHTML = '';

  pageData.forEach(entry => {
    const firstIn = new Date(Math.min(...entry.timeIns.map(t => t.getTime())));
    const lastOut = new Date(Math.max(...entry.timeOuts.map(t => t.getTime())));

    const spanSeconds = (lastOut - firstIn) / 1000;
    const workSeconds = entry.totalWorkMinutes * 60;
    const standbySeconds = spanSeconds - workSeconds;

    const totalQty = entry.totalFinished;
    const timePerUnit = totalQty > 0 ? (workSeconds / totalQty) : 0;

    const model = entry.model?.toUpperCase?.() || '';
    const targetCycleTime = parseFloat(cycleData?.[model] || 0);

    const mpeff = targetCycleTime && workSeconds > 0
      ? ((targetCycleTime * totalQty) / workSeconds) * 100
      : 0;

    const row = document.createElement('tr');
    row.innerHTML = `
      <td style="text-align: center;">${entry.date}</td>
      <td style="text-align: center;">${entry.person}</td>
      <td style="text-align: center;">${totalQty}</td>
      <td style="text-align: center;">${firstIn.toTimeString().slice(0, 5)}</td>
      <td style="text-align: center;">${lastOut.toTimeString().slice(0, 5)}</td>
      <td style="text-align: center;">
        ${workSeconds.toFixed(0)} sec
        ${standbySeconds > 0 ? ` (${standbySeconds.toFixed(0)} sec)` : ''}
      </td>

      <td style="text-align: center;">${targetCycleTime.toFixed(2)} sec</td>
      <td style="text-align: center;">${mpeff ? mpeff.toFixed(1) + '%' : '-'}</td>
    `;
    tbody.appendChild(row);
  });

  document.getElementById('last-updated').textContent = `Last updated: ${new Date().toLocaleString()}`;
}

Promise.all([
  fetch('api/assembly/getAssemblyData.php').then(res => res.json()),
  fetch('api/assembly/getManpowerRework.php').then(res => res.json()),
  fetch('api/mpeff_cycle/assembly.php').then(res => res.json())
])
.then(([assemblyData, reworkData, cycleData]) => {
  const mergedData = {};

 function addEntry(person, date, reference, timeIn, timeOut, finishedQty, source = 'assembly', model = '') {
  const key = `${person}_${date}`; // <-- changed this line

  if (!mergedData[key]) {
    mergedData[key] = {
      person,
      date,
      totalFinished: 0,
      timeIns: [],
      timeOuts: [],
      totalWorkMinutes: 0,
      model: model
    };
  }

  const group = mergedData[key];
  const timeInDate = new Date(timeIn);
  const timeOutDate = new Date(timeOut);

  if (model && !group.model) {
    group.model = model;
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
    const model = item.model || '';

    addEntry(item.person_incharge, day, item.reference_no, item.time_in, item.time_out, finishedQty, 'assembly', model);
  });

  reworkData.forEach(item => {
    if (!item.assembly_timeout || !item.assembly_timein || !item.assembly_person_incharge || !item.reference_no || !item.created_at) return;

    const day = extractDateOnly(item.created_at);
    const finishedQty = (parseInt(item.rework) || 0) + (parseInt(item.replace) || 0);
    const model = item.model || '';

    addEntry(item.assembly_person_incharge, day, item.reference_no, item.assembly_timein, item.assembly_timeout, finishedQty, 'rework', model);
  });

  mergedDataArray = Object.values(mergedData);
  filteredData = mergedDataArray.slice();

  paginator = createPaginator({
    data: filteredData,
    rowsPerPage: 10,
    renderPageCallback: (page) => renderPageCallback(page, cycleData),
    paginationContainerId: 'pagination'
  });

  paginator.render();
})
.catch(console.error);

function filterAndRender() {
  const col = filterColumn.value;
  const val = filterInput.value.toLowerCase();

  if (!col || !val) {
    filteredData = mergedDataArray.slice();
  } else {
    filteredData = mergedDataArray.filter(entry => {
      let field = '';
      switch (col) {
        case 'person': field = entry.person; break;
        case 'totalFinished': field = `${entry.totalFinished}`; break;
        case 'date': field = entry.date; break;
        case 'timeIn': field = entry.timeIns.map(d => d.toTimeString().slice(0, 5)).join(', '); break;
        case 'timeOut': field = entry.timeOuts.map(d => d.toTimeString().slice(0, 5)).join(', '); break;
        case 'timePerUnit': {
          const timePerUnit = entry.totalFinished > 0 ? (entry.totalWorkMinutes / entry.totalFinished) : 0;
          field = timePerUnit > 0 ? formatHoursMinutes(timePerUnit / 60) : '-';
          break;
        }
      }
      return field.toString().toLowerCase().includes(val);
    });
  }

  paginator.setData(filteredData);
}

// Filter controls
filterColumn.addEventListener('change', () => {
  filterInput.disabled = !filterColumn.value;
  filterInput.value = '';
  filterAndRender();
});

filterInput.addEventListener('input', () => {
  filterAndRender();
});

// Optional: initialize sorting
enableTableSorting(".table");
</script>
