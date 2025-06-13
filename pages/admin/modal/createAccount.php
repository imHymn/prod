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
              <option value="worker">Worker</option>
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

<script>
 

document.addEventListener("DOMContentLoaded", () => {
  const roleSelect = document.getElementById('role');
  const productionWrapper = document.getElementById('productionWrapper');
  const productionSelect = document.getElementById('production');
  const productionLocationWrapper = document.getElementById('productionLocationWrapper');

  document.getElementById('createAccountModal').addEventListener('show.bs.modal', () => {
    document.getElementById('createAccountForm').reset();
    productionWrapper.classList.add('d-none');
    productionLocationWrapper.classList.add('d-none');
  });

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
});
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
</script>