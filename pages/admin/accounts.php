
<?php include './components/reusable/tablesorting.php'; ?>
<?php include './components/reusable/tablepagination.php'; ?>
<?php include 'modal/createAccount.php'; ?>
<?php include 'modal/updateAccount.php'; ?>
<?php include 'modal/generateQRCode.php'; ?>


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

    <table class="table table-striped">
      <thead>
        <tr>
          <th class="col-md-2">Name <span class="sort-icon"></span></th>
          <th class="col-md-2">Role <span class="sort-icon"></span></th>
          <th class="col-md-2">Production <span class="sort-icon"></span></th>
          <th class="col-md-2">Username <span class="sort-icon"></span></th>
          <th class="col-md-2">Action</th> <!-- No sort icon, won't be sorted -->
        </tr>
      </thead>
      <tbody id="data-body"></tbody>
    </table>
<div id="pagination-controls" class="mt-2 text-center"></div>

      </div>
    </div>
  </div>
</div>

      </form>
    </div>
  </div>
</div>
<link href="assets/css/all.min.css"rel="stylesheet"></link>
<script src="assets/js/jquery.min.js"></script>
<script src="assets/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/sweetalert2@11.js"></script>
<link href="./assets/css/bootstrap-icons.css" rel="stylesheet">

<script>
// Role-based field toggling logic
const roleSelect = document.getElementById('role');
const productionWrapper = document.getElementById('productionWrapper');
const productionSelect = document.getElementById('production');
const productionLocationWrapper = document.getElementById('productionLocationWrapper');

function toggleFields() {
  const selectedRole = roleSelect.value;
  const selectedProduction = productionSelect.value;

  if (selectedRole === 'administrator' || selectedRole === 'user manager') {
    productionWrapper.classList.add('d-none');
    productionLocationWrapper.classList.add('d-none');
  } else if (selectedRole) {
    productionWrapper.classList.remove('d-none');
    if (selectedRole === 'line leader' && selectedProduction === 'stamping') {
      productionLocationWrapper.classList.remove('d-none');
    } else {
      productionLocationWrapper.classList.add('d-none');
    }
  } else {
    productionWrapper.classList.add('d-none');
    productionLocationWrapper.classList.add('d-none');
  }
}

roleSelect.addEventListener('change', () => {
  productionSelect.selectedIndex = 0;
  toggleFields();
});

productionSelect.addEventListener('change', toggleFields);
toggleFields();

// Account table rendering and pagination logic
let allUsers = [];
let paginator = null;

function renderTable(users) {
  const tbody = document.getElementById('data-body');
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
        <button class="btn btn-sm btn-outline-primary btn-update-user" 
                title="Update"
                data-user-id="${user.user_id}" 
                data-id="${user.id}">
          <i class="bi bi-pencil-square"style="font-size:16px"></i>
        </button>
         <button class="btn btn-sm btn-outline-danger btn-delete-user" 
                title="Delete"
                data-user-id="${user.user_id}" 
                data-id="${user.id}">
          <i class="bi bi-trash" style="font-size:16px"></i>
        </button>
      </td>
  
    `;
    tbody.appendChild(tr);
  });
  bindUpdateButtons(users);
  bindDeleteButtons(users);
}

function bindUpdateButtons(users) {
  const updateUserModal = new bootstrap.Modal(document.getElementById('updateUserModal'));

  document.querySelectorAll('.btn-update-user').forEach(button => {
    button.addEventListener('click', function () {
      const userId = this.getAttribute('data-user-id');
      const selectedUser = users.find(u => u.user_id === userId);
      if (!selectedUser) return;

      document.getElementById('modal-edit-id').value = selectedUser.id ?? '';
      document.getElementById('modal-edit-name').value = selectedUser.name ?? '';
      document.getElementById('modal-edit-user-id').value = selectedUser.user_id ?? '';
      document.getElementById('modal-edit-role').value = selectedUser.role ?? '';
      document.getElementById('modal-edit-production').value = selectedUser.production ?? '';
      document.getElementById('modal-edit-production-location').value = selectedUser.production_location ?? '';
      document.getElementById('modal-edit-password').value = selectedUser.password ?? '';

      updateUserModal.show();
    });
  });
  
}
function bindDeleteButtons(users) {
  document.querySelectorAll('.btn-delete-user').forEach(button => {
    button.addEventListener('click', function () {
      const userId = this.getAttribute('data-user-id');
      const id = this.getAttribute('data-id');

      const selectedUser = users.find(u => u.user_id === userId);
      if (!selectedUser) return;

      Swal.fire({
        title: `Delete ${selectedUser.name}?`,
        text: "This action cannot be undone.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, delete it!'
      }).then(result => {
        if (result.isConfirmed) {
          fetch('api/accounts/deleteAccount.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json'
            },
            body: JSON.stringify({ id: id })
          })
            .then(res => res.json())
            .then(response => {
              if (response.success) {
                Swal.fire('Deleted!', response.message, 'success');
                 loadAccounts(); // Refresh table
              } else {
                Swal.fire('Failed!', response.message, 'error');
              }
            })
            .catch(error => {
              console.error('Delete error:', error);
              Swal.fire('Error!', 'An error occurred while deleting.', 'error');
            });
        }
      });
    });
  });
}


function loadAccounts() {
  fetch('api/accounts/getAccounts.php')
    .then(res => res.json())
    .then(data => {
      allUsers = data;

      if (!paginator) {
        paginator = createPaginator({
          data: allUsers,
          rowsPerPage: 10,
          renderPageCallback: renderTable,
          paginationContainerId: 'pagination-controls'
        });
        paginator.render();
      } else {
        paginator.setData(allUsers);
      }
    })
    .catch(err => console.error('Error fetching accounts:', err));
}

const filterColumnSelect = document.getElementById('filter-column');
const filterInput = document.getElementById('filter-input');

filterColumnSelect.addEventListener('change', () => {
  filterInput.disabled = !filterColumnSelect.value;
  filterInput.value = '';
  paginator.setData(allUsers);
});

filterInput.addEventListener('input', () => {
  const filterCol = filterColumnSelect.value;
  const filterVal = filterInput.value.toLowerCase();

  if (!filterCol || !filterVal) {
    paginator.setData(allUsers);
    return;
  }

  const filteredUsers = allUsers.filter(user => {
    let fieldValue = user[filterCol] ?? '';
    if (filterCol === 'production') {
      fieldValue = (user.production ?? '') + ' ' + (user.production_location ?? '');
    }

    return fieldValue.toString().toLowerCase().includes(filterVal);
  });

  paginator.setData(filteredUsers);
});

loadAccounts();
enableTableSorting(".table");
</script>
