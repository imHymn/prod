<?php include './components/reusable/tablesorting.php'; ?>
<?php include './components/reusable/tablepagination.php'; ?>
<?php include './components/reusable/searchfilter.php'; ?>



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
                <option value="date">Date</option>
                <option value="person">Person Incharge</option>
                <option value="totalFinished">Quantity</option>
                <option value="timeIn">Time In</option>
                <option value="timeOut">Time Out</option>
                <option value="totalWorkingTime">Total Working Time</option>
                <option value="targetCycleTime">Target Cycle Time</option>
                <option value="actualCycleTime">Actual Cycle Time</option>
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
                <th style="width: 10%; text-align: center;">Actual Cycle Time <span class="sort-icon"></span></th>
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

    function renderPageCallback(pageData, cycleDataList) {
      tbody.innerHTML = '';
      console.log(pageData, cycleDataList);

      const cycleMap = {};
      Object.entries(cycleDataList).forEach(([materialNo, timeStr]) => {
        const time = parseFloat(timeStr);
        if (!isNaN(time)) {
          cycleMap[materialNo] = time;
        }
      });

      pageData.forEach(entry => {
        const firstIn = new Date(Math.min(...entry.timeIns.map(t => t.getTime())));
        const lastOut = new Date(Math.max(...entry.timeOuts.map(t => t.getTime())));
        const spanSeconds = (lastOut - firstIn) / 1000;
        const workSeconds = entry.totalWorkMinutes * 60;
        const standbySeconds = spanSeconds - workSeconds;

        Object.entries(entry.materialCount).forEach(([material, qty]) => {
          const cycle = cycleMap[material] || 0;
          const actual = (workSeconds > 0 && qty > 0) ? (workSeconds / qty).toFixed(2) : '-';

          const row = document.createElement('tr');
          row.innerHTML = `
        <td style="text-align: center;">${entry.date}</td>
        <td style="text-align: center;">${entry.person}</td>
        <td style="text-align: center;">${qty}</td>
        <td style="text-align: center;">${firstIn.toTimeString().slice(0, 5)}</td>
        <td style="text-align: center;">${lastOut.toTimeString().slice(0, 5)}</td>
        <td style="text-align: center;">
          ${workSeconds.toFixed(0)} sec
          ${standbySeconds > 0 ? ` (${standbySeconds.toFixed(0)} sec)` : ''}
        </td>
        <td style="text-align: center;">${cycle.toFixed(2)} sec</td>
        <td style="text-align: center;">${actual !== '-' ? `${actual} sec` : '-'}</td>
      `;
          tbody.appendChild(row);
        });
      });

      document.getElementById('last-updated').textContent =
        `Last updated: ${new Date().toLocaleString()}`;
    }


    Promise.all([
        fetch('api/assembly/getAssemblyData.php').then(res => res.json()),
        fetch('api/assembly/getManpowerRework.php').then(res => res.json()),
        fetch('api/mpeff_cycle/assembly_processtime.php').then(res => res.json())
      ])
      .then(([assemblyData, reworkData, cycleData]) => {

        console.log(cycleData)
        const mergedData = {};

        function addEntry(person, date, reference, timeIn, timeOut, finishedQty, material_no = '') {
          const key = `${person}_${date}`;
          if (!mergedData[key]) {
            mergedData[key] = {
              person,
              date,
              totalFinished: 0,
              timeIns: [],
              timeOuts: [],
              totalWorkMinutes: 0,
              materialCount: {} // ← Track material_no per group
            };
          }

          const group = mergedData[key];
          const timeInDate = new Date(timeIn);
          const timeOutDate = new Date(timeOut);

          if (!isNaN(timeInDate) && !isNaN(timeOutDate) && timeOutDate > timeInDate && finishedQty > 0) {
            const workedMin = (timeOutDate - timeInDate) / (1000 * 60);
            group.totalWorkMinutes += workedMin;
            group.timeIns.push(timeInDate);
            group.timeOuts.push(timeOutDate);
            group.totalFinished += finishedQty;

            if (material_no) {
              if (!group.materialCount[material_no]) {
                group.materialCount[material_no] = 0;
              }
              group.materialCount[material_no] += finishedQty;
            }
          }
        }

        assemblyData.forEach(item => {
          if (!item.time_out || !item.time_in || !item.person_incharge || !item.reference_no || !item.created_at)
            return;

          const day = extractDateOnly(item.created_at);
          const qty = parseInt(item.done_quantity) || 0;
          const mat = item.material_no || '';

          addEntry(item.person_incharge, day, item.reference_no, item.time_in, item.time_out, qty, mat);
        });

        reworkData.forEach(item => {
          if (!item.assembly_timeout || !item.assembly_timein || !item.assembly_person_incharge || !item.reference_no || !item.created_at)
            return;

          const day = extractDateOnly(item.created_at);
          const qty = (parseInt(item.rework) || 0) + (parseInt(item.replace) || 0);
          const mat = item.material_no || '';

          addEntry(item.assembly_person_incharge, day, item.reference_no, item.assembly_timein, item.assembly_timeout, qty, mat);
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
        setupSearchFilter({
          filterColumnSelector: '#filter-column',
          filterInputSelector: '#filter-input',
          data: mergedDataArray,
          onFilter: (filtered) => {
            filteredData = filtered;
            paginator.setData(filtered);
          },
          customValueResolver: (item, column) => {
            switch (column) {
              case 'date':
                return item.date;
              case 'person':
                return item.person;
              case 'totalFinished':
                return item.totalFinished;
              case 'timeIn':
                return item.timeIns.length ?
                  new Date(Math.min(...item.timeIns.map(t => new Date(t).getTime()))).toISOString() :
                  '';
              case 'timeOut':
                return item.timeOuts.length ?
                  new Date(Math.max(...item.timeOuts.map(t => new Date(t).getTime()))).toISOString() :
                  '';
              case 'totalWorkingTime':
                return (item.totalWorkMinutes * 60).toFixed(0); // in seconds
              case 'actualCycleTime': {
                const totalQty = item.totalFinished;
                const workSeconds = item.totalWorkMinutes * 60;
                return totalQty > 0 ? (workSeconds / totalQty).toFixed(2) : '';
              }
              case 'targetCycleTime': {
                // Calculate weighted average cycle time
                const totalQty = Object.values(item.materialCount).reduce((a, b) => a + b, 0);
                let totalWeighted = 0;
                for (const [mat, qty] of Object.entries(item.materialCount)) {
                  const cycle = parseFloat(cycleData[mat]?.assembly || 0);
                  totalWeighted += cycle * qty;
                }
                return totalQty > 0 ? (totalWeighted / totalQty).toFixed(2) : '';
              }
              default:
                return item[column] || '';
            }
          }
        });

      })
      .catch(console.error);




    enableTableSorting(".table");
  </script>