<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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
     <div class="d-flex justify-content-between align-items-center mb-3">
  <h6 class="card-title mb-0">Request Orders</h6>
</div>

<table class="table table" style="table-layout: fixed; width: 100%;">
  <thead>
    <tr>
    
      <th style="width: 10%; text-align: center;">Material No</th>
      <th style="width: 18%; text-align: center;">Component Name</th>

      <th style="width: 8%; text-align: center;">Quantity</th>
    <th style="width: 8%; text-align: center;">Stock Status</th>
    </tr>
  </thead>
  <tbody id="data-body"></tbody>
</table>


      
      </div>
    </div>
  </div>
</div>
<script>
fetch('api/rm/getPending.php')
  .then(response => response.json())
  .then(data => {
    console.log(data);

    const dataBody = document.getElementById('data-body');
    dataBody.innerHTML = ''; // Clear existing rows if any

    data.forEach(item => {
      const row = document.createElement('tr');

      // If status is "pending", show a button to send the request, include data-id here
      const statusContent = (item.status && item.status.toLowerCase() === 'pending') 
        ? `<button class="btn btn-sm btn-primary send-request-btn" 
                    data-id="${item.id}" 
                    data-material="${item.material_no}" 
                    data-component="${item.material_description}" 
                    data-quantity="${item.quantity}"
                    data-processQuantity ="${item.process_quantity}"
                    >
                 
             Send Request
           </button>`
        : `<span style="text-transform: uppercase;">${item.status || ''}</span>`;

      row.innerHTML = `
        <td style="text-align: center;">${item.material_no || ''}</td>
        <td style="text-align: center;">${item.material_description || ''}</td>
        <td style="text-align: center;">${item.quantity || 0}</td>
        <td style="text-align: center;">${statusContent}</td>
      `;

      dataBody.appendChild(row);
    });

    // Delegate click event on the buttons inside the tbody
    dataBody.addEventListener('click', function(e) {
      if (e.target.classList.contains('send-request-btn')) {
        const btn = e.target;
        const id = btn.getAttribute('data-id');               // <-- get id here
        const materialNo = btn.getAttribute('data-material');
        const componentName = btn.getAttribute('data-component');
        const quantity = btn.getAttribute('data-quantity');
        const processQuantity = btn.getAttribute('data-processQuantity');

        Swal.fire({
          title: 'Are you sure?',
          text: `Send request for ${quantity} units of ${componentName}?`,
          icon: 'question',
          showCancelButton: true,
          confirmButtonText: 'Yes, send it!',
          cancelButtonText: 'Cancel'
        }).then((result) => {
          if (result.isConfirmed) {
            // Prepare data to send including the id
            const postData = {
              id: id,
              material_no: materialNo,
              component_name: componentName,
              quantity: quantity,
              process_quantity:processQuantity
            };
            console.log(postData)
            fetch('api/rm/postRequest.php', {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json'
              },
              body: JSON.stringify(postData)
            })
            .then(response => {
              if (!response.ok) throw new Error(`HTTP error! Status: ${response.status}`);
              return response.json();
            })
            .then(data => {
              Swal.fire({
                icon: 'success',
                title: 'Request Sent',
                text: `Requested ${quantity} units of ${componentName}.`,
              })

              btn.disabled = true;
              btn.textContent = 'Sent';
            })
            .catch(error => {
              console.error('Error sending request:', error);
              Swal.fire({
                icon: 'error',
                title: 'Request Failed',
                text: 'There was an error sending your request. Please try again.',
              });
            });
          }
        });
      }
    });

  })
  .catch(error => {
    console.error('Error fetching data:', error);
  });
</script>
