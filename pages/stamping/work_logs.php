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
                <option value="date">Date</option>
                <option value="person">Person Incharge</option>
                <option value="section">Section</option>
                <option value="stage_name">Process</option>
                <option value="totalQuantity">Quantity</option>
                <option value="time_in">Time In</option>
                <option value="time_out">Time Out</option>
                <option value="working_time">Total Working Time</option>
                <option value="target_cycle">Target Cycle Time</option>
                <option value="actual_cycle">Actual Cycle Time</option>
              </select>

            </div>
            <div class="col-md-4">
              <input
                type="text"
                id="filter-input"
                class="form-control"
                placeholder="Type to filter..."
                disabled />
            </div>
          </div>
          <table class="table table" style="table-layout: fixed; width: 100%;">
            <thead>
              <tr>
                <th style="width: 7%; text-align: center;">Date <span class="sort-icon"></span></th>
                <th style="width: 15%; text-align: center;">Person Incharge <span class="sort-icon"></span></th>
                <th style="width: 7%; text-align: center;">Section <span class="sort-icon"></span></th>
                <th style="width: 7%; text-align: center;">Process <span class="sort-icon"></span></th>
                <th style="width: 7%; text-align: center;">Quantity <span class="sort-icon"></span></th>
                <th style="width: 7%; text-align: center;">Time In <span class="sort-icon"></span></th>
                <th style="width: 7%; text-align: center;">Time Out <span class="sort-icon"></span></th>
                <th style="width: 10%; text-align: center;">Total Working Time <span class="sort-icon"></span></th>
                <th style="width: 10%; text-align: center;">Target Cycle Time <span class="sort-icon"></span></th>
                <th style="width: 10%; text-align: center;">Actual Cycle Time <span class="sort-icon"></span></th>
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

    // Fetch cycle times and manpower data
    Promise.all([
      fetch('api/mpeff_cycle/stamping_processtime.php').then(res => res.json()),
      fetch('api/stamping/getManpowerData.php').then(res => res.json())
    ]).then(([cycleTimeData, manpowerData]) => {
      cycleTimes = cycleTimeData;
      fullData = manpowerData;
      console.log(cycleTimes, fullData)
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
      setupFiltering(sorted); // <-- call filtering setup after data is ready

    });

    function renderTable(data, page = 1) {
      const merged = {};
      console.log(data)
      data.forEach(item => {
        if (!item.person_incharge || !item.created_at) return;

        const createdDate = item.created_at.split(' ')[0];
        const stageName = item.stage_name || 'unknown';
        const key = `${item.section}_${stageName}_${item.person_incharge}_${createdDate}`;

        if (!merged[key]) {
          merged[key] = {
            person: item.person_incharge,
            section: item.section,
            stage_name: stageName,
            date: createdDate,
            material_no: item.material_no,
            components_name: item.components_name,
            totalFinished: 0,
            totalQuantity: 0,
            pendingQuantity: 0,
            timeIns: [],
            timeOuts: [],
            totalWorkMinutes: 0,
            references: new Set(),
            stage_names: new Set()
          };

        }



        const group = merged[key];
        const finishedQty = parseInt(item.process_quantity) || 0;
        const totalQty = parseInt(item.quantity) || 0;
        const pendingQty = parseInt(item.pending_quantity) || 0;

        group.totalFinished += finishedQty;
        group.totalQuantity += totalQty;
        group.pendingQuantity += pendingQty;

        const timeIn = item.time_in ? new Date(item.time_in) : null;
        const timeOut = item.time_out ? new Date(item.time_out) : null;
        if (item.stage_name) {
          group.stage_names.add(item.stage_name);
        }

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
        const normalize = str => str?.toLowerCase().replace(/[\s-]/g, '') || '';
        const sectionNormalized = normalize(group.section);
        const sessionLocationNormalized = normalize(sessionLocation);

        const canAccess =
          sessionRole === 'administrator' ||
          (sessionProduction.toLowerCase() === 'stamping' && sectionNormalized === sessionLocationNormalized);

        if (!canAccess) return;

        if (!groupedBySection[group.section]) {
          groupedBySection[group.section] = [];
        }
        groupedBySection[group.section].push(group);
      });

      Object.entries(groupedBySection).forEach(([section, groups]) => {
        // const sectionRow = document.createElement('tr');
        // sectionRow.innerHTML = `
        // <td colspan="9" style="background: #f0f0f0; font-weight: bold; text-align: left; padding: 8px;">
        //   Section: ${section}
        // </td>`;
        // dataBody.appendChild(sectionRow);

        groups.forEach(group => {
          if (group.timeIns.length === 0 || group.timeOuts.length === 0) return;

          const firstIn = new Date(Math.min(...group.timeIns.map(d => d.getTime())));
          const lastOut = new Date(Math.max(...group.timeOuts.map(d => d.getTime())));
          const spanMinutes = (lastOut - firstIn) / (1000 * 60);
          const standbyMinutes = spanMinutes - group.totalWorkMinutes;

          const totalWorkSeconds = group.totalWorkMinutes * 60;
          const standbySeconds = standbyMinutes * 60;

          // Get target cycle time based on parsed JSON stages
          let targetCycleTime = 0;
          const normalize = str => (typeof str === 'string' ? str.toLowerCase().replace(/[\s_-]/g, '') : '');

          for (const cycle of cycleTimes) {
            if (normalize(cycle.components_name) !== normalize(group.components_name)) continue;

            try {
              const parsedStages = JSON.parse(cycle.stage_name); // array of sections

              for (const sectionEntry of parsedStages) {
                const sectionMatch = normalize(sectionEntry.section) === normalize(group.section);
                if (!sectionMatch || !sectionEntry.stages) continue;

                for (const [stageName, value] of Object.entries(sectionEntry.stages)) {
                  const stageMatch = normalize(stageName) === normalize(group.stage_name); // assume group.stage_name is added
                  if (stageMatch) {
                    targetCycleTime = parseFloat(value);
                    break;
                  }
                }

                if (targetCycleTime > 0) break;
              }
            } catch (e) {
              console.warn('Invalid JSON in cycle.stage_name:', cycle.stage_name);
            }

            if (targetCycleTime > 0) break;
          }



          const actualCycleTime = (group.totalQuantity > 0) ?
            (totalWorkSeconds / group.totalQuantity) :
            0;


          const row = document.createElement('tr');
          row.innerHTML = `
  <td style="text-align: center;">${group.date}</td>
  <td style="text-align: center;">${group.person}</td>
  <td style="text-align: center;">${group.section}</td>
  <td style="text-align: center;">${group.stage_name}</td>
  <td style="text-align: center;">${group.totalQuantity || '<i>Null</i>'}</td>
  <td style="text-align: center;">${firstIn.toTimeString().slice(0, 5)}</td>
  <td style="text-align: center;">${lastOut.toTimeString().slice(0, 5)}</td>
  <td style="text-align: center;">${Math.round(totalWorkSeconds)}s (${Math.round(standbySeconds)}s)</td>
  <td style="text-align: center;">${targetCycleTime}s</td>
  <td style="text-align: center;">${actualCycleTime.toFixed(2)}s</td>
`;


          if (targetCycleTime === 0) {
            row.style.backgroundColor = '#ffe6e6';
            row.title = '⚠️ Missing or unmatched cycle time';
          }

          dataBody.appendChild(row);
        });
      });

      const now = new Date();
      document.getElementById('last-updated').textContent = `Last updated: ${now.toLocaleString()}`;
    }

    function setupFiltering(data) {
      const filterColumn = document.getElementById('filter-column');
      const filterInput = document.getElementById('filter-input');

      // Enable input when a column is selected
      filterColumn.addEventListener('change', () => {
        filterInput.disabled = false;
        filterInput.value = '';
      });

      // Filter as user types
      filterInput.addEventListener('input', () => {
        const column = filterColumn.value;
        const keyword = filterInput.value.trim().toLowerCase();

        if (!column) return;

        const filtered = data.filter(item => {
          switch (column) {
            case 'person':
              return item.person_incharge?.toLowerCase().includes(keyword);
            case 'section':
              return item.section?.toLowerCase().includes(keyword);
            case 'stage_name':
              return item.stage_name?.toLowerCase().includes(keyword);
            case 'totalQuantity':
              return (item.quantity + '').includes(keyword); // convert to string
            case 'date':
              return item.created_at?.split(' ')[0].includes(keyword);
            default:
              return true;
          }
        });

        paginator.setData(filtered);

      });
    }
  </script>