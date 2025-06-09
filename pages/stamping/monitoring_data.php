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
        <option value="person_incharge">Person Incharge</option>
        <option value="quantity">Quantity</option>
        <option value="total_quantity">Total Quantity</option>
        <option value="time_in">Time In</option>
        <option value="time_out">Time Out</option>
      </select>
    </div>
    <div class="col-md-4">
      <input
        type="text"
        id="filter-input"
        class="form-control"
        placeholder="Type to filter..."
        disabled
      />
    </div>
  </div>
<table class="table table" style="table-layout: fixed; width: 100%;">
  <thead>
    <tr>
      <th style="width: 7%; text-align: center;">Material No</th>
      
        <th style="width: 10%; text-align: center;">Person Incharge</th>
        <th style="width: 7%; text-align: center;">Date</th>
        <th style="width: 7%; text-align: center;">Total Quantity</th>
        <th style="width: 6%; text-align: center;">Time In</th>
        <th style="width: 6%; text-align: center;">Time Out</th>
        <th style="width: 15%; text-align: center;">Spent/Standby/Total Span</th>
        <th style="width: 10%; text-align: center;">UnitperMin</th>
        
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
function formatHoursMinutes(decimalHours) {
  const hours = Math.floor(decimalHours);
  const minutes = Math.round((decimalHours - hours) * 60);
  return `${hours} hrs${minutes > 0 ? ' ' + minutes + ' mins' : ''}`;
}

fetch('api/stamping/getManpowerData.php')
  .then(response => response.json())
  .then(data => {
    fullData = data;

    // Group and sort by reference and stage
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
  console.log(data);
  const merged = {};

data.forEach(item => {
  if (!item.person_incharge || !item.created_at) return;

  const createdDate = item.created_at.split(' ')[0];
  const key = `${item.person_incharge}_${createdDate}`; // <<< key changed

  if (!merged[key]) {
    merged[key] = {
      person: item.person_incharge,
      date: createdDate,
      material_no: item.material_no,
      totalFinished: 0,
      totalQuantity: 0,
      pendingQuantity: 0,
      timeIns: [],
      timeOuts: [],
      totalWorkMinutes: 0,
      references: new Set(), // optional
    };
  }

  const group = merged[key];

  const finishedQty = parseInt(item.process_quantity) || 0;
  group.totalFinished += finishedQty;

  const totalQty = parseInt(item.total_quantity) || 0;
  if (totalQty > group.totalQuantity) group.totalQuantity = totalQty;

  const pendingQty = parseInt(item.pending_quantity) || 0;
  group.pendingQuantity += pendingQty;

  const timeIn = item.time_in ? new Date(item.time_in) : null;
  const timeOut = item.time_out ? new Date(item.time_out) : null;

  if (timeIn && timeOut && timeOut > timeIn && finishedQty > 0) {
    const workedMinutes = (timeOut - timeIn) / (1000 * 60);
    group.totalWorkMinutes += workedMinutes;
    group.timeIns.push(timeIn);
    group.timeOuts.push(timeOut);
  }

  group.references.add(item.reference_no); // optional
});


  dataBody.innerHTML = ''; // Clear table

  Object.values(merged).forEach(group => {
    if (group.timeIns.length === 0 || group.timeOuts.length === 0) return;

    const firstIn = new Date(Math.min(...group.timeIns.map(d => d.getTime())));
    const lastOut = new Date(Math.max(...group.timeOuts.map(d => d.getTime())));
    const spanMinutes = (lastOut - firstIn) / (1000 * 60);
    const standbyMinutes = spanMinutes - group.totalWorkMinutes;
    const timePerUnit = group.totalFinished > 0 ? (group.totalWorkMinutes / group.totalFinished) : 0;

    const qtyDisplay = `${group.totalQuantity || '<i>Null</i>'}`;
    const timeInStr = firstIn.toTimeString().slice(0, 5);
    const timeOutStr = lastOut.toTimeString().slice(0, 5);
    const spentStr = formatHoursMinutes(group.totalWorkMinutes / 60);
    const standbyStr = formatHoursMinutes(standbyMinutes / 60);
    const spanStr = formatHoursMinutes(spanMinutes / 60);
    const timePerUnitStr = timePerUnit > 0 ? formatHoursMinutes(timePerUnit / 60) : '-';


    const row = document.createElement('tr');
    row.innerHTML = `
      <td style="text-align: center;">${group.material_no}</td>
      <td style="text-align: center;">${group.person}</td>
      <td style="text-align: center;">${group.date}</td>
      <td style="text-align: center;">${qtyDisplay}</td>
      <td style="text-align: center;">${timeInStr} </td>
      <td style="text-align: center;">${timeOutStr}</td>
      <td style="text-align: center;">${spentStr} / ${standbyStr} / ${spanStr}</td>
      <td style="text-align: center;">${timePerUnitStr}</td>

    `;

    dataBody.appendChild(row);
  });
}


// Enable input if column is selected
filterColumn.addEventListener('change', () => {
  filterInput.value = '';
  filterInput.disabled = !filterColumn.value;
  renderTable(fullData);
});

// Filter data based on selected column and input
filterInput.addEventListener('input', () => {
  const column = filterColumn.value;
  const value = filterInput.value.toLowerCase().trim();

  if (!column || value === '') {
    renderTable(fullData);
    return;
  }

  const filtered = fullData.filter(item => {
    const field = item[column];
    if (field === null || field === undefined) return false;
    return field.toString().toLowerCase().includes(value);
  });

  renderTable(filtered);
});
</script>