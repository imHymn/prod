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
            <div class="text-end mb-3">
              <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createAccountModal">
                Create Account
              </button>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#generateQR">
                Generate QR Code
              </button>
            </div>
          </div>
          <!-- Search Input -->
          <div class="mb-3">
            <input type="text" id="searchInput" class="form-control col-md-3" placeholder="Search by Username, Email, Section, Department...">
          </div>

          <!-- Account Table -->
          <table class="table table-bordered">
            <thead>
              <tr>
                <th class="col-md-2">User ID</th>
                <th class="col-md-2">Section</th>
                <th class="col-md-2">Department</th>
                <th class="col-md-3">Email</th>
              </tr>
            </thead>
            <tbody id="data-body"></tbody>
          </table>

        </div>
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
          <label for="section" class="form-label">Production</label>
          <select name="section" id="section" class="form-control">
            <option disabled selected>Choose production</option>
            <option value="delivery">Delivery</option>
            <option value="assembly">Assembly</option>
            <option value="qc">Quality Control</option>
            <option value="stamping">Stamping</option>
            <option value="fg_warehouse">FG Warehouse</option>
            <option value="rm_warehouse">RM Warehouse</option>
          </select>
        </div>
     
       <div class="mb-3">
        <label for="role" class="form-label">Role</label>
          <select name="role" id="role" class="form-control">
              <option disabled selected>Choose role</option>
              <option value="supervisor">Supervisor</option>
              <option value="administrator">Administrator</option>
               <option value="line leader">Line Leader</option>
              <option value="employee">Employee</option>
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



<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/sweetalert2@11.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
  const tbody = document.getElementById('data-body');
  const searchInput = document.getElementById('searchInput');
  const createAccountForm = document.getElementById('createAccountForm');
  const qrModal = new bootstrap.Modal(document.getElementById('generateQR'));
  const qrTextInput = document.getElementById('qrText');
  const qrCodeDiv = document.getElementById('qrcode');
  const generateBtn = document.getElementById('generateBtn');

  let allUsers = [];

  function renderTable(users) {
    tbody.innerHTML = '';
    users.forEach(user => {
      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td>${user.user_id}</td>
        <td>${user.section.toUpperCase() || '-'}</td>
        <td>${user.department.toUpperCase() || '-'}</td>
        <td>${user.email || '-'}</td>
      `;
      tbody.appendChild(tr);
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
  searchInput.addEventListener('input', () => {
    const keyword = searchInput.value.toLowerCase();
    const filtered = allUsers.filter(user =>
      (user.username || '').toLowerCase().includes(keyword) ||
      (user.email || '').toLowerCase().includes(keyword) ||
      (user.section || '').toLowerCase().includes(keyword) ||
      (user.department || '').toLowerCase().includes(keyword)
    );
    renderTable(filtered);
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
