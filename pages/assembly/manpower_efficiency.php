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
                <option value="" disabled selected>Select Column</option>
                <option value="date">Date</option>
                <option value="person">Person Incharge</option>
                <option value="totalFinished">Quantity</option>

                <option value="timeIn">Time In</option>
                <option value="timeOut">Time Out</option>
                <option value="spent">Total Working Time</option>
                <option value="standby">Target Cycle Time</option>
                <option value="span">Mpeff</option>
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
          <div id="pagination" class="mt-3 d-flex justify-content-center"></div>



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
      console.log(cycleData)
      // Extract merged cycle data
      const cycleTimesByMaterial = cycleData.materials || {};
      const spotCycleTime = parseFloat(cycleData.stamping_spotwelding) || 0;
      const finishCycleTime = parseFloat(cycleData.stamping_finishing) || 0;

      console.log(finishCycleTime, spotCycleTime)
      // Step 1: Group by person + date
      const grouped = {};

      pageData.forEach(entry => {
        const key = `${entry.date}_${entry.person || 'UNKNOWN'}`;

        if (!grouped[key]) {
          grouped[key] = {
            date: entry.date,
            person: entry.person || 'UNKNOWN',
            totalFinished: 0,
            totalWorkMinutes: 0,
            timeIns: [],
            timeOuts: [],
            material_no: entry.material_no,
            section: entry.section || ''
          };
        }

        const group = grouped[key];
        group.totalFinished += entry.totalFinished || 0;
        group.totalWorkMinutes += entry.totalWorkMinutes || 0;
        group.timeIns.push(...entry.timeIns);
        group.timeOuts.push(...entry.timeOuts);

        if (!group.material_no && entry.material_no) {
          group.material_no = entry.material_no;
        }
      });

      // Step 2: Render grouped results
      Object.values(grouped).forEach(entry => {
        const firstIn = new Date(Math.min(...entry.timeIns.map(t => new Date(t).getTime())));
        const lastOut = new Date(Math.max(...entry.timeOuts.map(t => new Date(t).getTime())));

        const spanSeconds = (lastOut - firstIn) / 1000;
        const workSeconds = entry.totalWorkMinutes * 60;
        const standbySeconds = spanSeconds - workSeconds;

        const totalQty = entry.totalFinished;
        const timePerUnit = totalQty > 0 ? (workSeconds / totalQty) : 0;

        const materialNo = entry.material_no?.toString?.().trim() || '';
        const section = entry.section || '';
        console.log(entry.section)
        let targetCycleTime = 0;

        if (section === 'FINISHING') {
          targetCycleTime = finishCycleTime;
        } else if (section === 'SPOT WELDING') {
          targetCycleTime = spotCycleTime;
        } else {
          targetCycleTime = parseFloat(cycleTimesByMaterial[materialNo] || 0);
        }

        // Fallbacks if no specific cycle time is found
        // const targetCycleTime = assemblyCycleTime || spotCycleTime || finishCycleTime;

        const timeInStr = firstIn.toTimeString().slice(0, 5);
        const timeOutStr = lastOut.toTimeString().slice(0, 5);

        const mpeff = targetCycleTime && workSeconds > 0 ?
          ((targetCycleTime * totalQty) / workSeconds) * 100 :
          0;

        const row = document.createElement('tr');
        row.innerHTML = `
      <td style="text-align: center;">${entry.date}</td>
      <td style="text-align: center; white-space: normal; word-wrap: break-word;">${entry.person}</td>
      <td style="text-align: center;">${totalQty}</td>
      <td style="text-align: center;">${timeInStr}</td>
      <td style="text-align: center;">${timeOutStr}</td>
      <td style="text-align: center;">
        ${workSeconds.toFixed(0)} sec
        ${standbySeconds > 0 ? ` (${standbySeconds.toFixed(0)} sec)` : ''}
      </td>
      <td style="text-align: center;">${targetCycleTime.toFixed(1)} sec</td>
      <td style="text-align: center;">${mpeff ? mpeff.toFixed(1) + '%' : '-'}</td>
    `;

        tbody.appendChild(row);
      });

      document.getElementById('last-updated').textContent = `Last updated: ${new Date().toLocaleString()}`;
    }




    Promise.all([
        fetch('api/assembly/getAssemblyData.php').then(res => res.json()),
        fetch('api/assembly/getManpowerRework.php').then(res => res.json()),
        fetch('api/mpeff_cycle/assembly_cycletime.php').then(res => res.json())
      ])
      .then(([assemblyData, reworkData, cycleData]) => {
        const mergedData = {};
        console.log(assemblyData)
        stampingData = assemblyData.stamping || []; // get the 'stamping' array
        assemblyData = assemblyData.assembly || []; // get the 'assembly' array


        function addEntry(person, date, reference, timeIn, timeOut, finishedQty, source = 'qc', material_no = '', material_description = '', good = 0, no_good = 0, lot = '', model = '', section = '') {

          const key = `${person}_${date}_${material_description}`;

          if (!mergedData[key]) {
            mergedData[key] = {
              person,
              date,
              totalFinished: 0,
              good: 0,
              no_good: 0,
              timeIns: [],
              timeOuts: [],
              totalWorkMinutes: 0,
              material_no,
              material_description,
              lot,
              model,
              section
            };
          }

          const group = mergedData[key];
          const timeInDate = new Date(timeIn);
          const timeOutDate = new Date(timeOut);

          if (material_no && !group.material_no) group.material_no = material_no;
          if (material_description && !group.material_description) group.material_description = material_description;
          if (lot && !group.lot) group.lot = lot;
          if (model && !group.model) group.model = model;

          if (!isNaN(timeInDate) && !isNaN(timeOutDate) && timeOutDate > timeInDate && finishedQty > 0) {
            const workedMin = (timeOutDate - timeInDate) / (1000 * 60);
            group.totalWorkMinutes += workedMin;
            group.timeIns.push(timeInDate);
            group.timeOuts.push(timeOutDate);
            group.totalFinished += finishedQty;
            group.good += good;
            group.no_good += no_good;
          }
        }



        // For QC data
        assemblyData.forEach(item => {
          if (!item.time_in || !item.time_out || !item.person_incharge || !item.reference_no || !item.created_at) return;

          const day = extractDateOnly(item.created_at);
          const finishedQty = parseInt(item.done_quantity) || 0;
          const material_no = item.material_no || '';
          const material_description = item.material_description || '';
          const good = parseInt(item.good) || 0;
          const no_good = parseInt(item.no_good) || 0;
          const lot = item.lot_no || '';
          const model = item.model || '';

          addEntry(item.person_incharge, day, item.reference_no, item.time_in, item.time_out, finishedQty, 'qc', material_no, material_description, good, no_good, lot, model);

        });

        // For Rework data
        reworkData.forEach(item => {
          if (!item.qc_timein || !item.qc_timeout || !item.qc_person_incharge || !item.reference_no || !item.created_at) return;

          const day = extractDateOnly(item.created_at);
          const finishedQty = parseInt(item.good) || 0;
          const material_no = item.material_no || '';
          const material_description = item.material_description || '';
          const good = parseInt(item.good) || 0;
          const no_good = parseInt(item.no_good) || 0;
          const lot = item.lot_no || '';
          const model = item.model || '';

          addEntry(item.qc_person_incharge, day, item.reference_no, item.qc_timein, item.qc_timeout, finishedQty, 'rework', material_no, material_description, good, no_good, lot, model);

        });
        // For Stamping data (FINISHING / SPOT WELDING only)
        stampingData.forEach(item => {
          if (!item.time_in || !item.time_out || !item.person_incharge || !item.reference_no || !item.created_at) return;

          const day = extractDateOnly(item.created_at);
          const finishedQty = parseInt(item.quantity) || 0;
          const material_no = item.material_no || '';
          const material_description = item.material_description || '';
          const good = parseInt(item.good) || 0;
          const no_good = parseInt(item.no_good) || 0;
          const lot = item.lot_no || '';
          const model = item.model || '';
          const section = item.section || '';

          addEntry(item.person_incharge, day, item.reference_no, item.time_in, item.time_out, finishedQty, 'stamping', material_no, material_description, good, no_good, lot, model, section);
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
          onFilter: filtered => {
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
                return item.timeIns?.length ? new Date(Math.min(...item.timeIns.map(t => new Date(t).getTime()))).toISOString() : '';
              case 'timeOut':
                return item.timeOuts?.length ? new Date(Math.max(...item.timeOuts.map(t => new Date(t).getTime()))).toISOString() : '';
              case 'spent': {
                const spanSeconds = item.timeOuts && item.timeIns ?
                  (Math.max(...item.timeOuts.map(t => new Date(t).getTime())) -
                    Math.min(...item.timeIns.map(t => new Date(t).getTime()))) / 1000 :
                  0;
                return spanSeconds;
              }
              case 'standby': {
                const targetCycleTime = parseFloat(cycleData?.[item.material_no?.toString().trim()] || 0);
                return targetCycleTime;
              }
              case 'span': {
                const totalQty = item.totalFinished;
                const workSeconds = item.totalWorkMinutes * 60;
                const targetCycleTime = parseFloat(cycleData?.[item.material_no?.toString().trim()] || 0);
                const mpeff = targetCycleTime && workSeconds > 0 ?
                  ((targetCycleTime * totalQty) / workSeconds) * 100 :
                  0;
                return mpeff;
              }
              default:
                return item[column] || '';
            }
          }
        });

      })

      .catch(console.error);

    // Optional: initialize sorting
    enableTableSorting(".table");
  </script>