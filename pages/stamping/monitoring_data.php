<?php
session_start();
$role = $_SESSION['role'];
$production = $_SESSION['production'];
$production_location = $_SESSION['production_location'];
?>

<script src="assets/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/sweetalert2@11.js"></script>
<script src="assets/js/html5.qrcode.js"></script>
<?php include './components/reusable/tablesorting.php'; ?>
<?php include './components/reusable/tablepagination.php'; ?>


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
     <div class="d-flex align-items-center justify-content-between mb-2">
   <h6 class="card-title mb-0">To-do List</h6>
  <small id="last-updated" class="text-muted" style="font-size:13px;"></small>
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
<div id="pagination" class="mt-3 d-flex justify-content-center"></div>

      
      </div>
    </div>
  </div>
</div>
<script>
let fullData = [];
let paginator;
let cycleTimes = {};
  const sessionRole = "<?php echo $role; ?>";
  const sessionProduction = "<?php echo $production; ?>";
  const sessionLocation = "<?php echo $production_location; ?>";

const dataBody = document.getElementById('data-body');
const filterColumn = document.getElementById('filter-column');
const filterInput = document.getElementById('filter-input');

// Fetch cycle times and manpower data
Promise.all([
  fetch('api/mpeff_cycle/stamping.php').then(res => res.json()),
  fetch('api/stamping/getManpowerData.php').then(res => res.json())
])
.then(([cycleTimeData, manpowerData]) => {
  cycleTimes = cycleTimeData;
  fullData = manpowerData;

  const grouped = {};
  manpowerData.forEach(item => {
    if (!grouped[item.reference_no]) grouped[item.reference_no] = [];
    grouped[item.reference_no].push(item);
  });

  const sorted = Object.values(grouped)
    .flatMap(group => group.sort((a, b) => parseInt(a.stage || 0) - parseInt(b.stage || 0)));

  fullData = sorted;

  paginator = createPaginator({
    data: sorted,
    rowsPerPage: 10,
    paginationContainerId: 'pagination',
    renderPageCallback: renderTable
  });

  paginator.render();
});

function renderTable(data, page = 1) {
  const merged = {};

  data.forEach(item => {
    if (!item.person_incharge || !item.created_at) return;

    const createdDate = item.created_at.split(' ')[0];
    const key = `${item.section}_${item.person_incharge}_${createdDate}`;

    if (!merged[key]) {
      merged[key] = {
        person: item.person_incharge,
        section: item.section,
        date: createdDate,
        material_no: item.material_no,
        totalFinished: 0,
        totalQuantity: 0,
        pendingQuantity: 0,
        timeIns: [],
        timeOuts: [],
        totalWorkMinutes: 0,
        references: new Set()
      };
    }

    const group = merged[key];
    const finishedQty = parseInt(item.process_quantity) || 0;
    group.totalFinished += finishedQty;

    const totalQty = parseInt(item.quantity) || 0;
    group.totalQuantity += totalQty;

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

    group.references.add(item.reference_no);
  });

  // Clear table
  dataBody.innerHTML = '';


const groupedBySection = {};
Object.values(merged).forEach(group => {
  // Normalize strings for comparison: lowercase, replace hyphens/spaces
  const normalize = str => str.toLowerCase().replace(/[\s-]/g, '');

  const sectionNormalized = normalize(group.section || '');
  const sessionLocationNormalized = normalize(sessionLocation || '');

  const canAccess =
    sessionRole === 'administrator' ||
    (sessionProduction.toLowerCase() === 'stamping' &&
     sectionNormalized === sessionLocationNormalized);

  if (!canAccess) return;

  if (!groupedBySection[group.section]) {
    groupedBySection[group.section] = [];
  }
  groupedBySection[group.section].push(group);
});


  // Render each section
  Object.keys(groupedBySection).forEach(section => {
    const groups = groupedBySection[section];

    // Insert section header row
    const sectionRow = document.createElement('tr');
    sectionRow.innerHTML = `
      <td colspan="8" style="background: #f0f0f0; font-weight: bold; text-align: left; padding: 8px;">
        Section: ${section}
      </td>
    `;
    dataBody.appendChild(sectionRow);

    groups.forEach(group => {
      if (group.timeIns.length === 0 || group.timeOuts.length === 0) return;

      const firstIn = new Date(Math.min(...group.timeIns.map(d => d.getTime())));
      const lastOut = new Date(Math.max(...group.timeOuts.map(d => d.getTime())));
      const spanMinutes = (lastOut - firstIn) / (1000 * 60);
      const standbyMinutes = spanMinutes - group.totalWorkMinutes;

      const totalWorkSeconds = group.totalWorkMinutes * 60;
      const standbySeconds = standbyMinutes * 60;

      const targetCycleTime = cycleTimes[section] || 0; // in seconds
      const timePerUnitSeconds = group.totalQuantity > 0
        ? totalWorkSeconds / group.totalQuantity
        : 0;

      const mpeff = (targetCycleTime && timePerUnitSeconds > 0)
        ? (targetCycleTime / timePerUnitSeconds) * 100
        : 0;

      const row = document.createElement('tr');
      row.innerHTML = `
        <td style="text-align: center;">${group.date}</td>
        <td style="text-align: center;">${group.person}</td>
        <td style="text-align: center;">${group.totalQuantity || '<i>Null</i>'}</td>
        <td style="text-align: center;">${firstIn.toTimeString().slice(0, 5)}</td>
        <td style="text-align: center;">${lastOut.toTimeString().slice(0, 5)}</td>
        <td style="text-align: center;">
          ${Math.round(totalWorkSeconds)}s  
          (${Math.round(standbySeconds)}s)
        </td>
        <td style="text-align: center;">${targetCycleTime}s</td>
        <td style="text-align: center;">${mpeff ? mpeff.toFixed(2) + '%' : '-'}</td>
      `;

      dataBody.appendChild(row);
    });
  });

  // Update timestamp
  const now = new Date();
  document.getElementById('last-updated').textContent = `Last updated: ${now.toLocaleString()}`;
}

</script>
