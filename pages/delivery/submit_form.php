  <script src="assets/js/sweetalert2@11.js"></script>
  <script src="assets/js/bootstrap.bundle.min.js"></script>
  <script src="assets/js/jquery.min.js"></script>
  <link rel="stylesheet" href="assets/css/choices.min.css" />
  <script src="assets/js/choices.min.js"></script>

  <div class="page-content">
    <nav class="page-breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="#">Pages</a></li>
        <li class="breadcrumb-item" aria-current="page">Delivery Submit Form</li>
      </ol>
    </nav>

    <div class="row">
      <div class="col-md-12 grid-margin stretch-card">
        <div class="card">
          <div class="card-body">
            <h6 class="card-title">Submit Form</h6>
            <form id="delivery_form">
              <div class="form-row">
                <div class="form-group col-md-2">
                  <label for="customer_name">Customer Name</label>
                  <select class="form-control" id="customerSelect" name="customer_name" required>
                  </select>
                </div>

                <div class="form-group col-md-2">
                  <label for="model_name">Model Name</label>
                  <select class="form-control" id="modelSelect" name="model_name" required>
                  </select>
                </div>

                <div class="form-group col-md-2">
                  <label for="lot">Lot No.</label>
                  <div class="input-group">

                    <input type="number" class="form-control text-center" id="lot" name="lot">

                  </div>
                </div>

                <div class="form-group col-md-2">
                  <label for="qty">QTY</label>
                  <div class="input-group">

                    <button type="button" class="btn btn-outline-secondary" id="increaseQty">+</button>
                    <input type="number" class="form-control text-center" id="qty" name="qty" value="0" readonly>
                    <button type="button" class="btn btn-outline-secondary" id="decreaseQty">-</button>
                  </div>
                </div>
                <div class="form-group col-md-2">
                  <label for="shifting">Shift Schedule</label>
                  <div class="input-group">
                    <select class="form-control" id="shifting" name="shifting" required>
                      <option disabled selected>Choose Shift:</option>
                      <option value="1st Shift">1st Shift</option>
                      <option value="2nd Shift">2nd Shift</option>
                    </select>
                  </div>
                </div>
                <div class="form-group col-md-2">
                  <label for="date_needed">Due Date</label>
                  <div class="input-group">
                    <input type='date' class='form-control form-control-sm' id='date_needed' name='date_needed' style='width: 160px;'>
                  </div>
                </div>
              </div>

              </style>
              <hr>
              <div id="title">
                <h6 class="card-title">COMPONENTS LIST</h6>
              </div>
              <div id="material_components">


              </div>

              <button id="delivery_submit_btn" type="button" class="btn btn-primary">Submit Request</button>
            </form>




          </div>
        </div>
      </div>
    </div>



    <script>
      document.addEventListener('DOMContentLoaded', function() {
        fetch('/mes/api/delivery/getCustomerAndModel.php')
          .then(response => response.json())
          .then(result => {

            const data = result.data;
            if (!Array.isArray(data)) {
              throw new Error('Invalid data format from API');
            }

            const customers = [...new Set(data.map(item => item.customer_name))];
            const models = [...new Set(data.map(item => item.model_name))];

            populateDropdown('customerSelect', customers);
            populateDropdown('modelSelect', models);
          })
          .catch(error => {
            console.error('Error fetching customer/model data:', error);
            Swal.fire('Error', 'Failed to load customer and model data.', 'error');
          });

        function populateDropdown(selectId, items) {
          const select = document.getElementById(selectId);
          select.innerHTML = '<option disabled selected>Select</option>';
          items.forEach(item => {
            const option = document.createElement('option');
            option.value = item;
            option.textContent = item;
            select.appendChild(option);
          });
        }
      });

      document.getElementById('modelSelect').addEventListener('change', handleModelOrCustomerChange);

      function handleModelOrCustomerChange() {
        const model = document.getElementById('modelSelect').value;
        const customer = document.getElementById('customerSelect').value;
        if (model) {
          fetch(`/mes/api/delivery/getLotNo.php?model_name=${encodeURIComponent(model)}`)
            .then(response => response.json())
            .then(result => {
              if (Array.isArray(result) && result.length > 0) {
                // Assuming lot_no is in the first object of the array
                const lotNo = parseInt(result[0].lot_no);
                document.getElementById('lot').value = lotNo > 0 ? lotNo : 1;
              } else {
                document.getElementById('lot').value = 1;

              }
            })
            .catch(error => {
              console.error('Error fetching lot number:', error);
              Swal.fire('Error', 'Something went wrong while retrieving lot number.', 'error');
            });
        }
        if (model && customer) {
          const params = new URLSearchParams({
            model_name: model,
            customer_name: customer
          });

          fetch(`/mes/api/delivery/getAllComponents.php?${params.toString()}`)
            .then(response => response.json())
            .then(result => {

              if (!Array.isArray(result) || result.length === 0) {
                document.getElementById('material_components').innerHTML = `
      <div class="alert alert-warning text-center">No components found for the selected model and customer.</div>
    `;
                return;
              }

              const table = document.createElement('table');
              table.className = 'table table-bordered table-striped';
              table.innerHTML = `
    <thead>
      <tr>
        <th>Material No.</th>
        <th>Material Description</th>
        <th>Supplement Order</th>
        <th class="text-center">Total Quantity</th>
      </tr>
    </thead>
    <tbody>
      ${result.map((row, index) => `
        <tr>
   
          <td class="materialNo">${row.material_no}</td>
          <td class="materialDesc">${row.material_description}</td>
           <td class="process" style="display: none;">${row.process || ''}</td>

          <td>
            <input type="number" class="form-control supplementInput" data-index="${index}" value="0" min="0" step="1" />
          </td>
          <td class="text-center">
            <span class="totalQty" id="totalQty${index}">0</span>
          </td>
        </tr>
      `).join('')}
    </tbody>
  `;

              const container = document.getElementById('material_components');
              container.innerHTML = '';
              container.appendChild(table);

              // Initialize supplement logic
              const qtyInput = document.getElementById('qty');
              const supplementInputs = container.querySelectorAll('.supplementInput');

              function updateAllTotalQuantities() {
                const baseQty = parseInt(qtyInput.value) || 0;
                supplementInputs.forEach(input => {
                  const idx = input.dataset.index;
                  const suppQty = parseInt(input.value) || 0;
                  const total = baseQty + suppQty;
                  const display = document.getElementById(`totalQty${idx}`);
                  if (display) display.textContent = total;
                });
              }

              // Bind events
              qtyInput.addEventListener('input', updateAllTotalQuantities);
              supplementInputs.forEach(input => {
                input.addEventListener('input', updateAllTotalQuantities);
              });

              updateAllTotalQuantities(); // Initial call


            })
            .catch(error => {
              console.error('Error fetching data:', error);
              Swal.fire('Error', 'Failed to fetch additional data.', 'error');
            });
        }
      }


      document.getElementById('delivery_submit_btn').addEventListener('click', function() {
        const currentQty = parseFloat(document.getElementById('qty').value) || 0;
        const rows = document.querySelectorAll('#material_components table tbody tr');
        const results = [];

        for (let i = 0; i < rows.length; i++) {
          const row = rows[i];
          const rawProcess = row.querySelector('.process')?.innerText;
          const process = rawProcess?.trim() || null;

          const material_no = row.querySelector('.materialNo')?.innerText.trim() || '';
          const material_description = row.querySelector('.materialDesc')?.innerText.trim() || '';
          const supplementInput = row.querySelector('.supplementInput');
          const supplementVal = parseFloat(supplementInput?.value) || 0;
          const totalQty = currentQty + supplementVal;

          const shift = document.getElementById('shifting').value;
          const lot_no = document.getElementById('lot').value || 0;
          const model_name = document.getElementById('modelSelect').value || '';
          const date_needed = document.getElementById('date_needed').value || '';

          if (material_no === '' || material_description === '') continue;

          results.push({
            material_no,
            model_name,
            material_description,
            quantity: currentQty,
            supplement_order: supplementInput?.value || '',
            total_quantity: totalQty,
            status: 'pending',
            section: 'DELIVERY',
            shift,
            lot_no,
            date_needed,
            process
          });
        }

        if (results.length === 0) {
          Swal.fire({
            icon: 'error',
            title: 'No Data',
            text: 'No valid materials found to submit.',
            confirmButtonColor: '#d33'
          });
          return;
        }

        const lot_value = document.getElementById('lot').value || 1;

        Swal.fire({
          title: 'Frozen Lot Warning',
          html: `The delivery form is at <strong>LOT - ${lot_value}</strong>. Do you want to proceed?`,
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: 'Yes, Confirm',
          cancelButtonText: 'Cancel',
          reverseButtons: true,
          customClass: {
            confirmButton: 'btn btn-primary',
            cancelButton: 'btn btn-secondary'
          },
          buttonsStyling: false
        }).then((result) => {
          if (result.isConfirmed) {
            fetch('/mes/api/delivery/postForms.php', {
                method: 'POST',
                headers: {
                  'Content-Type': 'application/json'
                },
                body: JSON.stringify(results)
              })
              .then(response => response.json())
              .then(responseData => {
                if (responseData.status === 'error') {
                  let itemList = responseData.insufficient_items?.map(item => `
              <li><strong>${item.material_no}</strong>: ${item.material_description}<br/><small>${item.reason}</small></li>
            `).join('');

                  Swal.fire({
                    icon: 'error',
                    title: 'Insufficient Stock',
                    html: `
                <p>${responseData.message}</p>
                <ul style="text-align:left; max-height: 300px; overflow-y: auto;">${itemList}</ul>
              `,
                    confirmButtonColor: '#d33'
                  });

                  return;
                }

                Swal.fire({
                  icon: 'success',
                  title: 'Success',
                  text: 'Your operation was successful!',
                  confirmButtonColor: '#3085d6'
                }).then(() => {
                  // Optionally reset or reload
                  // location.reload();
                });

                if (responseData?.length > 0) {
                  document.getElementById('lot').value = parseInt(responseData[0].lot_no) + 1;
                }
              })
              .catch(error => {
                console.error('Error posting data:', error);
                Swal.fire({
                  icon: 'error',
                  title: 'Network Error',
                  text: 'Failed to post data. Please try again.',
                  confirmButtonColor: '#d33'
                });
              });
          }
        });
      });






      document.getElementById('increaseQty').addEventListener('click', () => {
        const qtyInput = document.getElementById('qty');
        let val = parseInt(qtyInput.value) || 0;
        qtyInput.value = val + 30;
        qtyInput.dispatchEvent(new Event('input')); // trigger update
      });

      document.getElementById('decreaseQty').addEventListener('click', () => {
        const qtyInput = document.getElementById('qty');
        let val = parseInt(qtyInput.value) || 0;
        if (val >= 30) {
          qtyInput.value = val - 30;
          qtyInput.dispatchEvent(new Event('input')); // trigger update
        }
      });
    </script>