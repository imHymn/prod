<!-- accounts.php -->
<?php include 'generateQRCode.php'; ?>

<div class="page-content">
  <nav class="page-breadcrumb d-flex justify-content-between align-items-center">
    <ol class="breadcrumb mb-0">
      <li class="breadcrumb-item"><a href="#">Pages</a></li>
      <li class="breadcrumb-item" aria-current="page">Accounts</li>
    </ol>
  </nav>
<div class="row mt-3">
  <div class="col-md-12 grid-margin stretch-card">
    <div class="card">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h6 class="card-title mb-0">Account List</h6>
          <div>
            <button class="btn btn-primary me-2" data-bs-toggle="modal" data-bs-target="#createAccountModal">
              Create Account
            </button>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#generateQR">
              Generate QR Code
            </button>
          </div>
        </div>

        <!-- New row for filter controls -->
        <div class="row mb-3">
          <div class="col-md-3">
            <select id="filter-column" class="form-select">
              <option value="" disabled selected>Select Column to Filter</option>
              <option value="name">Name</option>
              <option value="role">Role</option>
              <option value="production">Production</option>
              <option value="user_id">User ID</option>
            </select>
          </div>
          <div class="col-md-3">
            <input
              type="text"
              id="filter-input"
              class="form-control"
              placeholder="Type to filter..."
              disabled
            />
          </div>
        </div>

        <!-- Account Table -->
        <table class="table table-striped">
          <thead>
            <tr>
              <th class="col-md-2">Name</th>
              <th class="col-md-2">Role</th>
              <th class="col-md-2">Production</th>
              <th class="col-md-2">Username</th>
              <th class="col-md-2">Action</th>
            </tr>
          </thead>
          <tbody id="data-body"></tbody>
        </table>

      </div>
    </div>
  </div>
</div>



<div class="modal fade" id="generateQR" tabindex="-1" aria-labelledby="generateQRLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title" id="generateQRLabel">QR Code Generator</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body text-center">
        <input type="text" id="qrText" class="form-control mb-3" placeholder="Enter text or URL">
        <div id="qrcode"></div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button id="generateBtn" type="button" class="btn btn-primary">Generate</button>
      </div>

    </div>
  </div>
</div>

<div class="modal fade" id="createAccountModal" tabindex="-1" aria-labelledby="createAccountModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered"> <!-- Added modal-dialog-centered -->
    <form id="createAccountForm" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="createAccountModalLabel">Create New Account</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label for="name" class="form-label">Full Name</label>
          <input type="text" id="name" name="name" class="form-control" required>
        </div>
        <div class="mb-3">
          <label for="user_id" class="form-label">User ID</label>
          <input type="text" id="user_id" name="user_id" class="form-control" required>
        </div>
        <div class="mb-3">
          <label for="password" class="form-label">Password</label>
          <input type="password" id="password" name="password" class="form-control" required minlength="5">
        </div>
               <div class="mb-3">
        <label for="role" class="form-label">Role</label>
          <select name="role" id="role" class="form-control">
              <option disabled selected>Choose role</option>
              <option value="user manager">User Manager</option>
              <option value="supervisor">Supervisor</option>
              <option value="administrator">Administrator</option>
              <option value="line leader">Line Leader</option>
          </select>
      </div>


<div class="mb-3 d-none" id="productionWrapper"> <!-- Fix: added id -->
  <label for="production" class="form-label">Production</label>
  <select name="production" id="production" class="form-control">
    <option disabled selected>Choose production</option>
    <option value="delivery">Delivery</option>
    <option value="assembly">Assembly</option>
    <option value="qc">Quality Control</option>
    <option value="stamping">Stamping</option>
    <option value="fg_warehouse">FG Warehouse</option>
    <option value="rm_warehouse">RM Warehouse</option>
  </select>
</div>




        <div class="mb-3 d-none" id="productionLocationWrapper">
        <label for="production_location" class="form-label">Production Location</label>
        <select name="production_location" id="production_location" class="form-control">
          <option disabled selected>Choose location</option>
          <option value="BIG-MECH">BIG-MECH</option>
          <option value="BIG-HYD">BIG-HYD</option>
          <option value="MUFFLER-COMPS">MUFFLER-COMPS</option>
          <option value="OEM-SMALL">OEM-SMALL</option>
        </select>
      </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary">Create Account</button>
      </div>
    </form>
  </div>
</div>


<!-- Update User Modal -->
<div class="modal fade" id="updateUserModal" tabindex="-1" aria-labelledby="updateUserModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form id="update-user-form">
        <div class="modal-header">
          <h5 class="modal-title" id="updateUserModalLabel">Update User</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label for="modal-edit-name" class="form-label">Name</label>
            <input type="text" class="form-control" id="modal-edit-name" />
          </div>
          <div class="mb-3">
            <label for="modal-edit-password" class="form-label">Password</label>
            <input type="password" class="form-control" id="modal-edit-password" />
          </div>

          <div class="mb-3">
            <label for="modal-edit-user-id" class="form-label">User ID</label>
            <input type="text" class="form-control" id="modal-edit-user-id" readonly />
          </div>
          <div class="mb-3">
            <label for="modal-edit-role" class="form-label">Role</label>
            <input type="text" class="form-control" id="modal-edit-role" />
          </div>
          <div class="mb-3">
            <label for="modal-edit-production" class="form-label">Production</label>
            <input type="text" class="form-control" id="modal-edit-production" />
          </div>
          <div class="mb-3">
            <label for="modal-edit-production-location" class="form-label">Production Location</label>
            <input type="text" class="form-control" id="modal-edit-production-location" />
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Save changes</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script src="assets/js/sweetalert2@11.js"></script>
<script>

document.addEventListener('DOMContentLoaded', () => {

  const roleSelect = document.getElementById('role');
  const productionWrapper = document.getElementById('productionWrapper');
  const productionSelect = document.getElementById('production');
  const productionLocationWrapper = document.getElementById('productionLocationWrapper');

  // On modal show, reset form and hide dependent selects
  document.getElementById('createAccountModal').addEventListener('show.bs.modal', () => {
    document.getElementById('createAccountForm').reset();
    productionWrapper.classList.add('d-none');          // Hide production initially
    productionLocationWrapper.classList.add('d-none');  // Hide production location initially
  });

  function toggleFields() {
    const selectedRole = roleSelect.value;
    const selectedProduction = productionSelect.value;

    if (selectedRole === 'administrator' || selectedRole === 'user manager' ) {
      // Hide production and production location selects for admin
      productionWrapper.classList.add('d-none');
      productionLocationWrapper.classList.add('d-none');
    } else if (selectedRole) {
      // Show production select for other roles (non-empty selection)
      productionWrapper.classList.remove('d-none');

      // Show production location only if role=line leader and production=stamping
      if (selectedRole === 'line leader' && selectedProduction === 'stamping') {
        productionLocationWrapper.classList.remove('d-none');
      } else {
        productionLocationWrapper.classList.add('d-none');
      }
    } else {
      // No role selected: hide all dependent selects
      productionWrapper.classList.add('d-none');
      productionLocationWrapper.classList.add('d-none');
    }
  }

  roleSelect.addEventListener('change', () => {
    // Reset production selection when role changes
    productionSelect.selectedIndex = 0;
    toggleFields();
  });

  productionSelect.addEventListener('change', toggleFields);

  // Initial call to set correct visibility on page load or modal open
  toggleFields();


  const tbody = document.getElementById('data-body');
  const searchInput = document.getElementById('searchInput');
  const createAccountForm = document.getElementById('createAccountForm');
  const qrModal = new bootstrap.Modal(document.getElementById('generateQR'));
  const qrTextInput = document.getElementById('qrText');
  const qrCodeDiv = document.getElementById('qrcode');
  const generateBtn = document.getElementById('generateBtn');

  let allUsers = [];
function renderTable(users) {
  const tbody = document.querySelector('tbody');
  tbody.innerHTML = '';
  users.forEach(user => {
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td>${user.name ?? 'null'}</td>
      <td>${user.role ? user.role.toUpperCase() : 'null'}</td>
      <td>
        ${user.production ? user.production.toUpperCase() : 'null'}
        ${user.production_location ? ' (' + user.production_location + ')' : ''}
      </td>
      <td>${user.user_id ?? 'null'}</td>
      <td>
        <button class="btn btn-sm btn-primary btn-update-user" data-user-id="${user.user_id}">
          Update
        </button>
      </td>
    `;
    tbody.appendChild(tr);
  });

 const updateUserModal = new bootstrap.Modal(document.getElementById('updateUserModal'));


  document.querySelectorAll('.btn-update-user').forEach(button => {
    button.addEventListener('click', function () {
      const userId = this.getAttribute('data-user-id');
      const selectedUser = users.find(u => u.user_id === userId);
      if (!selectedUser) return;

      // Populate modal inputs
      document.getElementById('modal-edit-name').value = selectedUser.name ?? '';
      document.getElementById('modal-edit-user-id').value = selectedUser.user_id ?? '';
      document.getElementById('modal-edit-role').value = selectedUser.role ?? '';
      document.getElementById('modal-edit-production').value = selectedUser.production ?? '';
      document.getElementById('modal-edit-production-location').value = selectedUser.production_location ?? '';
document.getElementById('modal-edit-password').value = selectedUser.password ?? '';

      // Show modal
      updateUserModal.show();
    });
  });
}





  function loadAccounts() {
    fetch('api/accounts/getAccounts.php')
      .then(res => res.json())
      .then(data => {
        allUsers = data;
        console.log('Loaded accounts:', allUsers);
        renderTable(allUsers);
      })
      .catch(err => console.error('Error fetching accounts:', err));
  }

  // SEARCH functionality
const filterColumnSelect = document.getElementById('filter-column');
const filterInput = document.getElementById('filter-input');

filterColumnSelect.addEventListener('change', () => {
  // Enable input only if a column is selected
  filterInput.disabled = !filterColumnSelect.value;
  filterInput.value = '';  // Reset input on column change
  renderTable(allUsers); // Reset table when column changes
});

filterInput.addEventListener('input', () => {
  const filterCol = filterColumnSelect.value;
  const filterVal = filterInput.value.toLowerCase();

  if (!filterCol || !filterVal) {
    renderTable(allUsers);
    return;
  }

  const filteredUsers = allUsers.filter(user => {
    // Access the property safely and normalize to string for search
    let fieldValue = user[filterCol] ?? '';
    
    // Special handling for production + production_location if filtering by production
    if (filterCol === 'production') {
      // Combine production and production_location for search
      fieldValue = (user.production ?? '') + ' ' + (user.production_location ?? '');
    }

    return fieldValue.toString().toLowerCase().includes(filterVal);
  });

  renderTable(filteredUsers);
});


  // CREATE ACCOUNT form submit
  createAccountForm.addEventListener('submit', (e) => {
    e.preventDefault();
    const formData = new FormData(createAccountForm);
    const data = Object.fromEntries(formData.entries());
  console.log('Form Data:', data); 
    fetch('/mes/api/accounts/createAccount.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(data)
    })
    .then(res => res.json())
    .then(response => {
      if (response.success) {
        Swal.fire({
          icon: 'success',
          title: 'Success',
          text: 'Your operation was successful!',
          confirmButtonColor: '#3085d6'
        }).then(() => {
          location.reload();
        });
      } else {
        Swal.fire('Error', response.message, 'error');
      }
    })
    .catch(err => {
      console.error('Request failed', err);
      Swal.fire('Error', 'Something went wrong.', 'error');
    });
  });

document.getElementById('generateQR').addEventListener('show.bs.modal', () => {
  qrCodeDiv.innerHTML = '';
  qrTextInput.value = '';

  const userSelect = document.getElementById('singleUserSelect');
  const userDetailsDiv = document.getElementById('userDetails');
  const detailEmail = document.getElementById('detailEmail');
  const detailDepartment = document.getElementById('detailDepartment');
  const detailSection = document.getElementById('detailSection');

  userSelect.innerHTML = '<option value="" disabled selected>Select User ID</option>';

  allUsers.forEach(user => {
    if (user.user_id) {
      const option = document.createElement('option');
      option.value = user.user_id;
      option.textContent = user.user_id;
      userSelect.appendChild(option);
    }
  });

  userDetailsDiv.style.display = 'none'; // hide initially
  console.log('Accounts data available for QR:', allUsers);
});

// When a user is selected, show their details
document.getElementById('singleUserSelect').addEventListener('change', function () {
  const selectedId = this.value;
  const user = allUsers.find(u => u.user_id === selectedId);

  if (user) {
function renderDetail(id, value) {
    const element = document.getElementById(id);
    if (value && value.trim() !== '') {
        element.textContent = value;
    } else {
        element.innerHTML = '<em>None</em>';
    }
}

renderDetail('detailName', user.name);
renderDetail('detailEmail', user.email);
renderDetail('detailSection', user.section);
renderDetail('detailDepartment', user.department);

document.getElementById('userDetails').style.display = 'block';

  }
});



  // Generate QR code only when button is clicked
  generateBtn.addEventListener('click', () => {
    const text = qrTextInput.value.trim();
    if (!text) {
      Swal.fire('Warning', 'Please enter text or URL to generate QR code.', 'warning');
      return;
    }
    qrCodeDiv.innerHTML = '';
    // Using QRCode.js or similar library, assuming generateQRCode function is available
    new QRCode(qrCodeDiv, {
      text: text,
      width: 200,
      height: 200,
    });
  });

  loadAccounts();
});

</script>
