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
                <th style="width: 5%; text-align: center;">Material No</th>
                <th style="width: 10%; text-align: center;">Component Name</th>
                <th style="width: 20%; text-align: center;">Raw Materials</th>
                <th style="width: 10%; text-align: center;">Status</th>
                <th style="width: 10%; text-align: center;">Action</th>
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

      fetch('/mes/api/rm/getIssuedComponents.php')
        .then(response => response.json())
        .then(responseData => {
          if (Array.isArray(responseData.data)) {
            renderIssuedComponentsTable(responseData.data);
          } else {
            console.warn('Unexpected response:', responseData);
          }
        });



      function renderIssuedComponentsTable(data) {

        const tbody = document.getElementById('data-body');
        tbody.innerHTML = '';

        if (data.length === 0) {
          tbody.innerHTML = `<tr><td colspan="5" class="text-center">No pending requests found.</td></tr>`;
          return;
        }

        // Define status priority (lower = higher priority)
        const statusPriority = {
          'Critical': 1,
          'Minimum': 2,
          'Reorder': 3,

        };

        // Sort the data array by status priority
        data.sort((a, b) => {
          // Determine effective status for a and b considering the 1-day "Critical" override:
          function effectiveStatus(item) {
            let s = item.status || 'Pending';
            if (!item.delivered_at && item.issued_at) {
              const issuedDate = new Date(item.issued_at);
              const now = new Date();
              const diffInDays = (now - issuedDate) / (1000 * 60 * 60 * 24);
              if (diffInDays >= 1) {
                s = 'Critical';
              }
            }
            return s;
          }

          const statusA = effectiveStatus(a);
          const statusB = effectiveStatus(b);

          const priorityA = statusPriority[statusA] ?? 99;
          const priorityB = statusPriority[statusB] ?? 99;

          return priorityA - priorityB;
        });

        const statusStyleMap = {
          'Maximum': 'color: green; font-weight: bold; text-shadow: -1px -1px 0 #004d00, 1px -1px 0 #004d00, -1px 1px 0 #004d00, 1px 1px 0 #004d00;',
          'Critical': 'color: red; font-weight: bold; text-shadow: -1px -1px 0 #800000, 1px -1px 0 #800000, -1px 1px 0 #800000, 1px 1px 0 #800000;',
          'Minimum': 'color: orange; font-weight: bold; text-shadow: -1px -1px 0 #cc6600, 1px -1px 0 #cc6600, -1px 1px 0 #cc6600, 1px 1px 0 #cc6600;',
          'Reorder': 'color: yellow; font-weight: bold; text-shadow: -1px -1px 0 #999900, 1px -1px 0 #999900, -1px 1px 0 #999900, 1px 1px 0 #999900;'
        };
        data.forEach(item => {
          let status = item.status || 'Pending';

          if (!item.delivered_at && item.issued_at) {
            const issuedDate = new Date(item.issued_at);
            const now = new Date();
            const diffInDays = (now - issuedDate) / (1000 * 60 * 60 * 24);
            if (diffInDays >= 1) {
              status = 'Critical';
            }
          }

          const style = statusStyleMap[status] || '';

          const baseValue = 300;
          const quantity = item.usage_type * baseValue;


          const rawMaterials = (() => {
            try {
              const all = JSON.parse(item.raw_materials || '[]');
              return all.filter(rm =>
                rm.component_name === item.component_name
              );
            } catch {
              return [];
            }
          })();

          console.log(item)
          const rawHTML = rawMaterials.length ? `
  <table class="table table-sm table-bordered mb-0" style="margin:0; table-layout: fixed; width: 100%;">
    <thead>
      <tr>
        <th style="font-size:10px; padding:2px; width:15%;">No</th>
        <th style="font-size:10px; padding:2px; width:50%;">Desc</th>
        <th style="font-size:10px; padding:2px; width:10%;">Usage</th>
        <th style="font-size:10px; padding:2px; width:10%;">Total</th>   <!-- NEW -->
      </tr>
    </thead>
    <tbody>
      ${rawMaterials.map(rm => {
        const usage  = Number(rm.usage) || 0;          // per-unit usage
        const total  = Math.ceil(usage * quantity);    // round-UP total
        return `
          <tr>
            <td style="font-size:10px; padding:2px;">${rm.material_no}</td>
            <td style="font-size:10px; padding:2px;">${rm.material_description}</td>
            <td style="font-size:10px; padding:2px;">${usage}</td>
            <td style="font-size:10px; padding:2px;">${total}</td>       <!-- NEW -->
          </tr>
        `;
      }).join('')}
    </tbody>
  </table>` : '<em style="font-size:12px;">None</em>';


          const info = {
            id: item.id,
            material_no: item.material_no,
            component_name: item.component_name,
            quantity,
            process_quantity: item.process_quantity ?? 300,
            stage_name: item.stage_name,
            raw_materials: item.raw_materials,
            usage: item.usage_type
          };

          const row = document.createElement('tr');
          row.innerHTML = `
    <td class="text-center">${item.material_no || '-'}</td>
    <td class="text-center">
      ${item.component_name || '-'}
    </td>    <td class="text-center align-middle">${rawHTML}</td>
    <!--<td class="text-center">${quantity}</td>-->

    <td class="text-center" style="${style}">${status.toUpperCase()}</td>
    <td class="text-center align-middle">
      <button class="btn btn-sm btn-success deliver-btn"
        data-info="${encodeURIComponent(JSON.stringify(info))}">
        Deliver
      </button>
    </td>
  `;

          tbody.appendChild(row);
        });


        attachDeliverButtonEvents();
      }



      function attachDeliverButtonEvents() {
        document.querySelectorAll('.deliver-btn').forEach(button => {
          button.addEventListener('click', function() {
            const info = JSON.parse(decodeURIComponent(this.dataset.info || '{}'));

            const {
              id,
              material_no,
              component_name,
              quantity,
              process_quantity,
              stage_name,
              raw_materials,
              usage
            } = info;

            const rawMaterials = (() => {
              try {
                const all = JSON.parse(raw_materials || '[]');
                return all.filter(rm => rm.component_name === component_name);
              } catch {
                return [];
              }
            })();

            const baseInput = 300 * usage;

            function buildRawMaterialList(qty) {
              if (!rawMaterials.length) return '<em>No raw materials listed.</em>';
              return `
    <table class="table table-sm table-bordered mt-2">
      <thead>
        <tr>
          <th style="font-size:12px;">Material No</th>
          <th style="font-size:12px;">Description</th>
          <th style="font-size:12px;">Usage</th>
          <th style="font-size:12px;">Total</th>
        </tr>
      </thead>
      <tbody>
        ${rawMaterials.map(rm => {
          const usage = Number(rm.usage) || 0;
          const total = Math.ceil(usage * qty);
          return `
            <tr>
              <td style="font-size:12px;">${rm.material_no}</td>
              <td style="font-size:12px;">${rm.material_description}</td>
              <td style="font-size:12px;">${usage}</td>
              <td style="font-size:12px;">${total}</td>
            </tr>
          `;
        }).join('')}
      </tbody>
    </table>
  `;
            }


            Swal.fire({
              title: 'Confirm Issue',
              html: `
    <p>Are you sure you want to issue these raw materials, which are equivalent to <strong>${baseInput}</strong> items of <strong>${component_name}</strong>?</p>
    <hr/>
    ${buildRawMaterialList(baseInput)}
  `,
              icon: 'warning',
              showCancelButton: true,
              confirmButtonText: 'Yes, Issue it',
              cancelButtonText: 'Change Quantity'
            }).then(result => {
              if (result.isConfirmed) {
                sendIssueRequest({
                  id,
                  material_no,
                  component_name,
                  quantity: baseInput,
                  process_quantity,
                  stage_name
                });
              } else if (result.dismiss === Swal.DismissReason.cancel) {
                Swal.fire({
                  title: 'Custom Quantity',
                  input: 'number',
                  inputValue: baseInput,
                  inputLabel: `Enter base quantity for ${component_name}:`,
                  inputAttributes: {
                    min: 1,
                    max: baseInput,
                    step: 1
                  },
                  showCancelButton: true,
                  preConfirm: (inputVal) => {
                    const customQty = parseInt(inputVal);
                    if (isNaN(customQty) || customQty <= 0) {
                      Swal.showValidationMessage('Please enter a valid positive number');
                      return false;
                    }
                    return Swal.fire({
                      title: 'Confirm Custom Quantity',
                      html: `
                  <p>You will issue <strong>${customQty}</strong> items for <strong>${component_name}</strong>.</p>
                  <hr/>
                  ${buildRawMaterialList(customQty)}
                `,
                      icon: 'info',
                      showCancelButton: true,
                      confirmButtonText: 'Proceed',
                      cancelButtonText: 'Cancel'
                    }).then(confirmRes => {
                      if (confirmRes.isConfirmed) {
                        sendIssueRequest({
                          id,
                          material_no,
                          component_name,
                          quantity: customQty,
                          process_quantity,
                          stage_name
                        });
                      }
                    });
                  }
                });
              }
            });
          });
        });
      }



      function sendIssueRequest(data) {

        fetch('/mes/api/rm/postIssuedComponent.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
          })
          .then(res => res.json())
          .then(response => {
            if (response.status === 'success') {
              Swal.fire('Success', response.message || 'Issued successfully.', 'success').then(() => {

              });
            } else {
              Swal.fire('Error', response.message || 'Issue failed.', 'error');
            }
          })
          .catch(error => {
            console.error('Error:', error);
            Swal.fire('Error', 'Something went wrong.', 'error');
          });
      }
    });
  </script>