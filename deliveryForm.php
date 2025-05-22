
<div class="page-content">
  <nav class="page-breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="#">Pages</a></li>
      <li class="breadcrumb-item" aria-current="page">Delivery Request Form</li>
    </ol>
  </nav>

  <div class="row">
    <div class="col-md-12 grid-margin stretch-card">
      <div class="card">
        <div class="card-body">
          <h6 class="card-title">Request Form</h6>

  <form id="delivery_form">
  <div class="form-row">
     <!-- <div class="form-group col-md-3">
  <label for="qty">Handler Name</label>
  <div class="input-group">

    <input type="text" class="form-control text-center" id="name" name="name">
  
  </div>
</div> -->
    <div class="form-group col-md-2">
      <label for="customer_name">Customer Name</label>
      <select class="form-control" id="customerSelect" name="customer_name" required>
      
        <!-- Add more customer options here -->
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
<!-- Confirmation Modal -->


<script src="assets/js/sweetalert2@11.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />

<!-- Choices.js JS -->
<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    let choicesInstance = null;  // Track the Choices instance for SKU

    // Use fetch to make the GET request to get customer data
    fetch('api/delivery/getCustomers.php')
        .then(response => response.json())  // Parse the JSON response
        .then(customers => {
            // Get the <select> element for customers and models
            const customerSelect = document.getElementById('customerSelect');
            const modelSelect = document.getElementById('modelSelect');
         
            const skuOption = document.getElementById('choices-sku'); // Get the SKU select element

            // Clear any existing options in the customer dropdown (if any)
            customerSelect.innerHTML = '<option value="">Select a customer</option>';

            // Loop through each customer and create an option element
            customers.forEach(function (customer) {
                const option = document.createElement('option');
                option.value = customer.customer_name;  // Use 'customer_name' as the value
                option.textContent = customer.customer_name;  // Use 'customer_name' as the display text
                customerSelect.appendChild(option);  // Add the option to the customer select dropdown
            });

            // Add an event listener to handle customer selection changes
            customerSelect.addEventListener('change', function () {
                const selectedCustomer = customerSelect.value;  // Get the selected customer
                if (selectedCustomer) {
                    // Fetch models based on the selected customer
                    fetch(`api/delivery/getModels.php?customer=${selectedCustomer}`)
                        .then(response => response.json())  // Parse the JSON response
                        .then(models => {
                            // Clear the existing options in the model select dropdown
                              document.getElementById('material_components').innerHTML = '';
                            modelSelect.innerHTML = '<option value="">Select a model</option>';

                            // Loop through each model and create an option element
                            models.forEach(function (model) {
                                const option = document.createElement('option');
                                option.value = model.model_name;  // Use 'model_name' as the value
                                option.textContent = model.model_name;  // Use 'model_name' as the display text
                                modelSelect.appendChild(option);  // Add the option to the model select dropdown
                  
                            });
                       
                        })
                        .catch(error => {
                            console.error('Error fetching models:', error);  // Log any errors
                        });

                } else {
                    // If no customer is selected, clear the models and SKU dropdowns
                    modelSelect.innerHTML = '<option value="">Select a model</option>';
                    skuOption.innerHTML = ''; // Clear SKU options
                }
            });

            // Model change event listener
            modelSelect.addEventListener('change', function () {
                const selectedModel = modelSelect.value;
                const customerValue = customerSelect.value;
                 
                              
                                  fetch(`api/delivery/getLotNo.php?model_name=${selectedModel}`)
                                  .then(response => response.json())  // Parse the JSON response
                                  .then(lotNo => {
                                     console.log(lotNo[0].lot_no)
                                       document.getElementById('lot').value = lotNo[0].lot_no + 1;



                                  })
                                  .catch(error => {
                                      console.error('Error fetching models:', error);  // Log any errors
                                  });


                if (selectedModel && customerValue) {
                    // Fetch models based on the selected customer
                    fetch(`api/delivery/getSku.php?customer=${customerValue}&model=${selectedModel}`)
                        .then(response => response.json()) 
                        .then(data => {
                         document.getElementById('material_components').innerHTML = data.tableHtml;


const inputQty = document.getElementById('qty');
const btnIncrease = document.getElementById('increaseQty');
const btnDecrease = document.getElementById('decreaseQty');
const container = document.getElementById('material_components');
const qtyValues = [];
const totalQty = [];

function updateTotalQtys() {
  const totalQtyElements = container.getElementsByClassName('totalQty');
  const supplementInputs = container.querySelectorAll('input[id^="supplement"]');
  const currentQty = parseFloat(inputQty.value) || 0;

  qtyValues.length = 0;
  totalQty.length = 0;

  for (let i = 0; i < totalQtyElements.length; i++) {
    const supplementVal = parseFloat(supplementInputs[i]?.value) || 0;

    qtyValues[i] = {
      qty: currentQty,
      supplement: supplementVal
    };

    totalQty[i] = currentQty + supplementVal;

    totalQtyElements[i].innerText = totalQty[i];
  }
}

btnIncrease.addEventListener('click', function () {
  inputQty.value = parseFloat(inputQty.value || 0) + 30;
  updateTotalQtys();
});

btnDecrease.addEventListener('click', function () {
  inputQty.value = Math.max(0, parseFloat(inputQty.value || 0) - 30); // Prevent negative values
  updateTotalQtys();
});



container.addEventListener('input', function(event) {
    const target = event.target;
    if (target.matches('input[id^="supplement"]')) {
        const totalQtyElements = container.getElementsByClassName('totalQty');
        const supplementInputs = container.querySelectorAll('input[id^="supplement"]');
        const currentQty = parseFloat(inputQty.value) || 0;

        qtyValues.length = 0;
        totalQty.length = 0;

        for (let i = 0; i < totalQtyElements.length; i++) {
            const supplementVal = parseFloat(supplementInputs[i]?.value) || 0;

            qtyValues[i] = {
                qty: currentQty,
                supplement: supplementVal
            };

            totalQty[i] = currentQty + supplementVal;

            totalQtyElements[i].innerText = totalQty[i];
        }

  
    }
});


                        })
                        .catch(error => {
                            console.error('Error fetching models:', error);  // Log any errors
                        });

                } else {
                  
                }
            
    
     
            });
        })
        .catch(error => {
            console.error('Error fetching customers:', error);  // Log any errors
        });



document.getElementById('delivery_submit_btn').addEventListener('click', function() {
      const lot_value = document.getElementById('lot').value || + 1;

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
            const currentQty = parseFloat(document.getElementById('qty').value) || 0;
            const rows = document.querySelectorAll('#material_components table tr'); // all rows in your table
            const results = [];

            
            for (let i = 1; i < rows.length; i++) {
                const row = rows[i];
                const material_no = row.querySelector('.materialNo')?.innerText.trim() || '';
                const material_description = row.querySelector('.materialDesc')?.innerText.trim() || '';
                const supplementInput = row.querySelector('input[id^="supplement"]');
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
                    supplement_order: supplementInput?.value || '', // keep it as string if empty
                    total_quantity: totalQty,
                    status:'pending',
                    section:'DELIVERY',
                    shift,
                    lot_no,
                    date_needed
                });

            }

                console.log('Compiled values:', results);

                fetch('api/delivery/postForms.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(results)  // send array as JSON
                })
                .then(response => response.json())
                .then(responseData => {
                    console.log('Server response:', responseData);
                     Swal.fire({
  icon: 'success',
  title: 'Success',
  text: 'Your operation was successful!',
  confirmButtonColor: '#3085d6'
}).then(() => {
  location.reload(); // Reload the page
});;

                    if (responseData?.length > 0) {
                        document.getElementById('lot').value = parseInt(responseData[0].lot_no) + 1;
                    }
                })
                .catch(error => {
                    console.error('Error posting data:', error);
                });

        } else {
            console.log('Action canceled.');
        }
    });
   
 
});


});

</script>

