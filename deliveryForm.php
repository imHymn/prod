
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
    <div class="form-group col-md-3">
      <label for="customer_name">Customer Name</label>
      <select class="form-control" id="customerSelect" name="customer_name" required>
      
        <!-- Add more customer options here -->
      </select>
    </div>

    <div class="form-group col-md-3">
      <label for="model_name">Model Name</label>
      <select class="form-control" id="modelSelect" name="model_name" required>
       
     
        <!-- Add more model options here -->
      </select>
    </div>

  
  
   <div class="form-group col-md-3">
      <label for="qty">QTY</label>
      <input type="number" class="form-control" id="qty" name="qty" placeholder="Enter qty" required  >
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

                    // Clear the SKU choices when customer is changed
                    if (choicesInstance) {
                        choicesInstance.destroy();  // Destroy previous Choices instance if exists
                    }

                    // Initialize a new Choices instance for SKU dropdown
                    choicesInstance = new Choices(skuOption, {
                        removeItemButton: true,
                        placeholderValue: 'Select SKU',
                    });

                    choicesInstance.clearChoices();  // Clear existing choices
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
             


                if (selectedModel && customerValue) {
                    // Fetch models based on the selected customer
                    fetch(`api/delivery/getSku.php?customer=${customerValue}&model=${selectedModel}`)
                        .then(response => response.json()) 
                        .then(data => {
                         document.getElementById('material_components').innerHTML = data.tableHtml;


const qtyValues = [];
const totalQty = [];
const inputQty = document.getElementById('qty');
const container = document.getElementById('material_components');

inputQty.addEventListener('input', function () {
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

    console.log('qtyValues (qty input):', qtyValues);
    console.log('totalQty (qty input):', totalQty);
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

        console.log('qtyValues (supplement input):', qtyValues);
        console.log('totalQty (supplement input):', totalQty);
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
    const currentQty = parseFloat(document.getElementById('qty').value) || 0;
    const rows = document.querySelectorAll('#material_components table tr'); // all rows in your table
    const results = [];

    // Start from 1 to skip header row
    for (let i = 1; i < rows.length; i++) {
        const row = rows[i];
        const materialNumber = row.querySelector('.materialNo')?.innerText.trim() || '';
        const materialDescription = row.querySelector('.materialDesc')?.innerText.trim() || '';
        const supplementInput = row.querySelector('input[id^="supplement"]');
        const supplementVal = parseFloat(supplementInput?.value) || 0;
        const totalQty = currentQty + supplementVal;

          if (materialNumber === '' || materialDescription === '') continue;

        results.push({
            materialNumber,
            materialDescription,
            qty: currentQty,
            supplementOrder: supplementInput?.value || '', // keep it as string if empty
            totalQuantity: totalQty
        });

    }

    console.log('Compiled values:', results);

    // You can send this data via fetch/ajax or further process it here
});


    // Handle form submission
    // document.getElementById('delivery_submit_btn').addEventListener('click', function () {
    //     const selectedSkus = choicesInstance.getValue(true); // Always get current values
    //     console.log(selectedSkus);
    //     fetch('/your-endpoint', {
    //         method: 'POST',
    //         headers: {
    //             'Content-Type': 'application/json',
    //         },
    //         body: JSON.stringify({ Skus: selectedSkus }),
    //     })
    //         .then(response => response.json())
    //         .then(data => {
    //             console.log('Server response:', data);
    //         })
    //         .catch(error => {
    //             console.error('Error:', error);
    //         });
    // });
});

</script>

