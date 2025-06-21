<script src="assets/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/sweetalert2@11.js"></script>
<?php include './components/reusable/tablesorting.php'; ?>
<?php include './components/reusable/tablepagination.php'; ?>
<?php include './components/reusable/searchfilter.php'; ?>

<div class="page-content">
  <nav class="page-breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="#">Pages</a></li>
      <li class="breadcrumb-item" aria-current="page">Inventory</li>
    </ol>
  </nav>

  <div class="row">
    <div class="col-md-12 grid-margin stretch-card">
      <div class="card">
        <div class="card-body">

          <div class="d-flex align-items-center justify-content-between mb-2">
            <h6 class="card-title mb-0">Request Orders</h6>
            <small id="last-updated" class="text-muted" style="font-size:13px;"></small>
          </div>

          <div class="row mb-3">
            <div class="col-md-3">
              <select id="filter-column" class="form-select">
                <option value="" disabled selected>Select Column</option>
                <option value="material_no">Material No</option>
                <option value="components_name">Component Name</option>
                <option value="actual_inventory">Inventory</option>
                <option value="statusLabel">Status</option>
              </select>
            </div>
            <div class="col-md-4">
              <input type="text" id="filter-input" class="form-control" placeholder="Type to filter..." />
            </div>
          </div>

          <table class="table" style="table-layout: fixed; width: 100%;">
            <thead>
              <tr>
                <th style="width: 10%; text-align: center;">Material No <span class="sort-icon"></span></th>
                <th style="width: 18%; text-align: center;">Component Name <span class="sort-icon"></span></th>
                <th style="width: 8%; text-align: center;">Quantity <span class="sort-icon"></span></th>
                <th style="width: 8%; text-align: center;">Status <span class="sort-icon"></span></th>
                <th style="width: 8%; text-align: center;">Action</th> <!-- No sort icon for action -->
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
    document.addEventListener('DOMContentLoaded', function() {
      let inventoryData = []; // All inventory records
      let paginator = null;

      fetch('api/rm/getIssued.php')
        .then(response => response.json())
        .then(issuedResponse => {
          const pendingIssuedSet = new Set(
            issuedResponse.data
            .filter(entry => entry.status === 'pending')
            .map(entry => `${entry.material_no}|${entry.component_name}`)
          );

          fetch('api/rm/getComponents.php')
            .then(response => response.json())
            .then(components => {
              inventoryData = components
                .filter(item => {
                  const uniqueKey = `${item.material_no}|${item.components_name}`;
                  return !pendingIssuedSet.has(uniqueKey); // Exclude already issued
                })
                .map(item => {
                  const inventory = item.actual_inventory;
                  const reorder = item.reorder;
                  const critical = item.critical;
                  const minimum = item.minimum;
                  const normal = item.normal;
                  const maximum = item.maximum_inventory;
                  const uniqueKey = `${item.material_no}|${item.components_name}`;
                  const alreadyIssued = pendingIssuedSet.has(uniqueKey);
                  let statusLabel = '';
                  let statusColor = '';
                  let showRequestButton = false;

                  if (inventory >= maximum) {
                    statusLabel = "Maximum";
                    statusColor = "green";
                    showRequestButton = true;
                  } else if (inventory <= critical && inventory < minimum) {
                    statusLabel = "Critical";
                    statusColor = "red";
                    showRequestButton = true;
                  } else if (inventory <= minimum && inventory > critical) {
                    statusLabel = "Minimum";
                    statusColor = "orange";
                    showRequestButton = true;
                  } else if (inventory <= reorder && inventory < normal) {
                    statusLabel = "Reorder";
                    statusColor = "yellow";
                    showRequestButton = true;
                  } else if (inventory >= normal) {
                    statusLabel = "Normal";
                    statusColor = "green";
                  } else {
                    statusLabel = "Reorder";
                    statusColor = "yellow";
                    showRequestButton = true;
                  }

                  const actionContent = alreadyIssued ?
                    `<button class="btn btn-sm btn-secondary" disabled>Issued</button>` :
                    showRequestButton ?
                    `<button class="btn btn-sm btn-primary send-request-btn"
                        data-id="${item.id}" 
                        data-material="${item.material_no}" 
                        data-component="${item.components_name}" 
                        data-quantity="${item.actual_inventory}"
                        data-usage_type="${item.usage_type}"
                        data-process_quantity="${item.process_quantity}"
                        data-stage_name='${JSON.stringify(item.stage_name)}'>
                      Issue
                    </button>` :
                    `<span class="text-muted">-</span>`;

                  return {
                    ...item,
                    statusLabel,
                    statusColor,
                    actionContent
                  };
                });

              // 🔥 Sort data by updated_at DESC and then status priority
              const statusPriority = {
                "Critical": 1,
                "Minimum": 2,
                "Reorder": 3,
                "Normal": 4,
                "Maximum": 5
              };

              inventoryData.sort((a, b) => {
                const updatedA = new Date(a.updated_at || 0).getTime();
                const updatedB = new Date(b.updated_at || 0).getTime();

                if (updatedB !== updatedA) {
                  return updatedB - updatedA;
                }

                const priorityA = statusPriority[a.statusLabel] || 99;
                const priorityB = statusPriority[b.statusLabel] || 99;
                return priorityA - priorityB;
              });

              paginator = createPaginator({
                data: inventoryData,
                rowsPerPage: 10,
                paginationContainerId: 'pagination',
                renderPageCallback: renderInventoryTable // <- your existing render function
              });
              paginator.render();
              setupSearchFilter({
                filterColumnSelector: '#filter-column',
                filterInputSelector: '#filter-input',
                data: inventoryData,
                onFilter: filtered => paginator.setData(filtered),
                customValueResolver: (item, column) => {
                  switch (column) {
                    case 'material_no':
                      return item.material_no ?? '';
                    case 'components_name':
                      return item.components_name ?? '';
                    case 'actual_inventory':
                      return item.actual_inventory?.toString() ?? '';
                    case 'statusLabel':
                      return item.statusLabel ?? '';
                    default:
                      return item[column] ?? '';
                  }
                }
              });

              document.getElementById('data-body').addEventListener('click', function(e) {
                if (e.target.classList.contains('send-request-btn')) {
                  const btn = e.target;
                  const id = btn.dataset.id;
                  const materialNo = btn.dataset.material;
                  const componentName = btn.dataset.component;
                  const quantity = parseInt(btn.dataset.quantity) || 0;
                  const usageType = parseInt(btn.dataset.usage_type) || 0;
                  const process_quantity = parseInt(btn.dataset.process_quantity) || 1;
                  const stage_name = JSON.parse(btn.dataset.stage_name);
                  const calculatedQty = 300 * usageType;

                  Swal.fire({
                    title: 'Confirm Issue',
                    html: `Are you sure you want to issue <strong>${calculatedQty}</strong> items for <strong>${componentName}</strong>?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Issue it',
                    cancelButtonText: 'Change Quantity'
                  }).then(result => {
                    if (result.isConfirmed) {
                      sendIssueRequest({
                        id,
                        material_no: materialNo,
                        component_name: componentName,
                        quantity: calculatedQty,
                        process_quantity,
                        stage_name
                      });
                    } else if (result.dismiss === Swal.DismissReason.cancel) {
                      Swal.fire({
                        title: 'Custom Quantity',
                        input: 'number',
                        inputValue: calculatedQty,
                        inputLabel: `Enter quantity for ${componentName}:`,
                        inputAttributes: {
                          min: 1,
                          max: calculatedQty
                        },
                        showCancelButton: true
                      }).then(inputRes => {
                        if (inputRes.isConfirmed) {
                          sendIssueRequest({
                            id,
                            material_no: materialNo,
                            component_name: componentName,
                            quantity: parseInt(inputRes.value),
                            process_quantity,
                            stage_name
                          });
                        }
                      });
                    }
                  });
                }
              });

              const now = new Date();
              document.getElementById('last-updated').textContent = `Last updated: ${now.toLocaleString()}`;
            });
        });

      function renderInventoryTable(items) {
        const dataBody = document.getElementById('data-body');
        dataBody.innerHTML = '';
        items.forEach(item => {
          const row = document.createElement('tr');
          row.innerHTML = `
          <td style="text-align: center;">${item.material_no}</td>
          <td style="text-align: center;">${item.components_name}</td>
          <td style="text-align: center;">${item.actual_inventory}</td>
          <td style="text-align: center;">
            <span style="color: ${item.statusColor};">${item.statusLabel}</span>
          </td>
          <td style="text-align: center;">${item.actionContent}</td>
        `;
          dataBody.appendChild(row);
        });
      }

      function sendIssueRequest(data) {
        fetch('api/rm/postIssuedComponent.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
          })
          .then(response => response.json())
          .then(result => {
            if (result.status === 'success') {
              Swal.fire('Success', 'Component issued successfully.', 'success');
            } else {
              Swal.fire('Error', result.message || 'Failed to issue component.', 'error');
            }
          })
          .catch(error => {
            console.error('Issue Request Error:', error);
            Swal.fire('Error', 'Something went wrong while issuing the component.', 'error');
          });
      }

      enableTableSorting(".table"); // Optional
    });
  </script>