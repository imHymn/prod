<?php include './components/reusable/tablesorting.php'; ?>
<?php include './components/reusable/tablepagination.php'; ?>
<?php include './components/reusable/searchfilter.php'; ?>

<script src="assets/js/html5.qrcode.js"></script>

<script src="assets/js/sweetalert2@11.js"></script>
<script src="assets/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/jquery.min.js"></script>
<link rel="stylesheet" href="assets/css/choices.min.css" />
<script src="assets/js/choices.min.js"></script>
<script>

</script>

<div class="page-content">
  <nav class="page-breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="#">Pages</a></li>
      <li class="breadcrumb-item" aria-current="page">Delivery Section</li>
    </ol>
  </nav>

  <div class="row">
    <div class="col-md-12 grid-margin stretch-card">
      <div class="card">
        <div class="card-body">
          <div class="d-flex align-items-center justify-content-between mb-2">
            <h6 class="card-title mb-0">List of Pulled Out</h6>
            <small id="last-updated" class="text-muted" style="font-size:13px;"></small>
          </div>

          <div class="row mb-3">
            <div class="col-md-3">
              <select id="column-select" class="form-select">
                <option value="" disabled selected>Select Column</option>

                <option value="material_no">Material No</option>
                <option value="model_name">Model</option>
                <option value="quantity">Qty</option>
                <option value="supplement_order">Supplement</option>
                <option value="total_quantity">Total Qty</option>
                <option value="shift">Shift</option>
                <option value="lot_no">Lot</option>
                <option value="date_needed">Date Needed</option>
                <option value="date_loaded">Date Loaded</option>
                <option value="truck">Truck</option>
              </select>
            </div>
            <div class="col-md-4">
              <input type="text" id="search-input" class="form-control" placeholder="Type to filter..." />
            </div>
          </div>



          <table class="table table" style="table-layout: fixed; width: 100%;">
            <thead>
              <tr>
                <th style="width: 10%; text-align: center;">Material No <span class="sort-icon"></span></th>
                <!-- <th style="width: 20%; text-align: center;">Description</th> -->
                <th style="width: 10%; text-align: center;">Model <span class="sort-icon"></span></th>
                <th style="width: 8%; text-align: center;">Qty <span class="sort-icon"></span></th>
                <th style="width: 8%; text-align: center;">Supplement <span class="sort-icon"></span></th>
                <th style="width: 8%; text-align: center;">Total Qty <span class="sort-icon"></span></th>
                <th style="width: 8%; text-align: center;">Shift <span class="sort-icon"></span></th>
                <th style="width: 8%; text-align: center;">Lot <span class="sort-icon"></span></th>
                <th style="width: 15%; text-align: center;">Date Needed <span class="sort-icon"></span></th>
                <th style="width: 15%; text-align: center;">Date Loaded <span class="sort-icon"></span></th>
                <th style="width: 10%; text-align: center;">Truck <span class="sort-icon"></span></th>
                <th style="width: 10%; text-align: center;">Status <span class="sort-icon"></span></th>
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
    let paginator;
    let originalData = [];
    let filteredData = [];

    document.addEventListener('DOMContentLoaded', () => {
      fetch('api/delivery/getPulled_out.php')
        .then(res => res.json())
        .then(data => {
          document.getElementById('last-updated').textContent = `Last updated: ${new Date().toLocaleString()}`;
          originalData = data;
          filteredData = data;

          paginator = createPaginator({
            data: filteredData,
            rowsPerPage: 10,
            paginationContainerId: 'pagination',
            renderPageCallback: renderPulledOutTable,
            defaultSortFn: (a, b) => new Date(b.date_needed) - new Date(a.date_needed)
          });
          paginator.render();

          setupSearchFilter({
            filterColumnSelector: '#column-select',
            filterInputSelector: '#search-input',
            data: originalData,
            onFilter: (filtered) => {
              filteredData = filtered;
              paginator.setData(filteredData);
            }
          });
        })
        .catch(console.error);
    });


    function renderPulledOutTable(pageData) {
      const tbody = document.getElementById('data-body');
      tbody.innerHTML = ''; // Clear existing rows

      pageData.forEach(item => {
        const row = document.createElement('tr');

        const actionButton = item.action ?
          `<button class="btn btn-sm btn-success" disabled>DONE</button>` :
          `<button class="btn btn-sm btn-warning" onclick='handleAction(${JSON.stringify(item)})'>CONFIRM</button>
`;

        row.innerHTML = `
      <td style="text-align: center;">${item.material_no}</td>
      <td style="text-align: center;">${item.model_name}</td>
      <td style="text-align: center;">${item.quantity}</td>
      <td style="text-align: center;">${item.supplement_order ?? '0'}</td>
      <td style="text-align: center;">${item.total_quantity}</td>
      <td style="text-align: center;">${item.shift}</td>
      <td style="text-align: center;">${item.lot_no}</td>
      <td style="text-align: center;">${item.date_needed || '<i>NONE</i>'}</td>
      <td style="text-align: center;">${item.date_loaded || '<i>NONE</i>'}</td>
      <td style="text-align: center;">${item.truck || '<i>NONE</i>'}</td>
      <td style="text-align: center;">${actionButton}</td>
    `;
        tbody.appendChild(row);
      });
    }

    function handleAction(item) {
      fetch('/mes/api/delivery/getTruck.php')
        .then(res => res.json())
        .then(truckList => {
          const options = truckList.map(truck =>
            `<option value="${truck.name}">${truck.name}</option>`
          ).join('');

          Swal.fire({
            title: 'Confirm Action',
            html: `
          <p><strong>Material No:</strong> ${item.material_no}</p>
          <p><strong>Component:</strong> ${item.material_description}</p>
          <p>Are you sure you want to confirm?</p>
          <label for="truck-select">Select Truck:</label>
          <select id="truck-select" class="swal2-input">
            <option value="" disabled selected>Select a truck</option>
            ${options}
          </select>
        `,
            focusConfirm: false,
            showCancelButton: true,
            confirmButtonText: 'Yes, Confirm',
            cancelButtonText: 'Cancel',
            preConfirm: () => {
              const truck = document.getElementById('truck-select').value;
              if (!truck) {
                Swal.showValidationMessage('Please select a truck');
                return false;
              }
              return truck;
            }
          }).then(result => {
            if (result.isConfirmed) {
              const selectedTruck = result.value;

              // Send all needed data
              fetch('/mes/api/delivery/postAction.php', {
                  method: 'POST',
                  headers: {
                    'Content-Type': 'application/json'
                  },
                  body: JSON.stringify({
                    id: item.id,
                    truck: selectedTruck,
                    material_no: item.material_no,
                    model_name: item.model_name,
                    material_description: item.material_description,
                    total_quantity: item.total_quantity
                  })
                })
                .then(res => res.json())
                .then(data => {
                  console.log(data);
                  Swal.fire('Success', 'Action confirmed and truck selected.', 'success');
                })
                .catch(error => {
                  console.error('Post error:', error);
                  Swal.fire('Error', 'Something went wrong.', 'error');
                });
            }
          });
        })
        .catch(error => {
          console.error('Truck fetch error:', error);
          Swal.fire('Error', 'Failed to load truck list.', 'error');
        });
    }


    enableTableSorting(".table");
  </script>