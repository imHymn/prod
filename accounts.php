<!-- accounts.php -->

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
          <label for="user_id" class="form-label">User ID</label>
          <input type="text" id="user_id" name="user_id" class="form-control" required>
        </div>
        <div class="mb-3">
          <label for="password" class="form-label">Password</label>
          <input type="password" id="password" name="password" class="form-control" required minlength="6">
        </div>
        <div class="mb-3">
          <label for="email" class="form-label">Email</label>
          <input type="email" id="email" name="email" class="form-control" required>
        </div>
        <div class="mb-3">
          <label for="section" class="form-label">Section</label>
          <select name="section" id="section" class="form-control">
            <option disabled selected>Choose section</option>
            <option value="delivery">Delivery</option>
            <option value="assembly">Assembly</option>
            <option value="qc/qa">Quality Control</option>
            <option value="stamping">Stamping</option>
            <option value="warehouse">Warehouse</option>
          </select>
        </div>
        <div class="mb-3">
          <label for="department" class="form-label">Department</label>
          
            <select name="department" id="department" class="form-control">
                <option disabled selected>Choose department</option>
                <option value="muf">Muffler</option>
                <option value="rad">Radiator</option>
                <option value="qc/qa">Quality Control</option>
                 <option value="ls">Logistics</option>
                
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
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/sweetalert2@11.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
  const tbody = document.getElementById('data-body');
  const searchInput = document.getElementById('searchInput');
  const createAccountForm = document.getElementById('createAccountForm');

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
        renderTable(allUsers);
      })
      .catch(err => console.error('Error fetching accounts:', err));
  }

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

  createAccountForm.addEventListener('submit', (e) => {
    e.preventDefault();

    const formData = new FormData(createAccountForm);
    const data = Object.fromEntries(formData.entries());

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
  location.reload(); // Reload the page
});;
  } else {
    Swal.fire('Error', response.message, 'error');
  }
})

    .catch(err => {
      console.error('Request failed', err);
      Swal.fire('Error', 'Something went wrong.', 'error');
    });
  });

  loadAccounts(); // initial data load
});
</script>
