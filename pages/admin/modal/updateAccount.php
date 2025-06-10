<div class="modal fade" id="updateUserModal" tabindex="-1" aria-labelledby="updateUserModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form id="update-user-form">
        <div class="modal-header">
          <h5 class="modal-title" id="updateUserModalLabel">Update User</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body">
          <input type="hidden" id="modal-edit-id" />

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
            <select id="modal-edit-role" class="form-control">
              <option disabled selected>Choose role</option>
              <option value="user manager">User Manager</option>
              <option value="supervisor">Supervisor</option>
              <option value="administrator">Administrator</option>
              <option value="line leader">Line Leader</option>
            </select>
          </div>

          <div class="mb-3">
            <label for="modal-edit-production" class="form-label">Production</label>
            <select id="modal-edit-production" class="form-control">
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
            <label for="modal-edit-production-location" class="form-label">Production Location</label>
            <select id="modal-edit-production-location" class="form-control">
              <option disabled selected>Choose location</option>
              <option value="BIG-MECH">BIG-MECH</option>
              <option value="BIG-HYD">BIG-HYD</option>
              <option value="MUFFLER-COMPS">MUFFLER-COMPS</option>
              <option value="OEM-SMALL">OEM-SMALL</option>
            </select>
          </div>
        </div> <!-- end modal-body -->

        <!-- Optional: Add a modal-footer with submit button -->
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Save Changes</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        </div>
      </form>
    </div> <!-- end modal-content -->
  </div> <!-- end modal-dialog -->
</div> <!-- end modal -->

<script>

document.getElementById('update-user-form').addEventListener('submit', function(event) {
  event.preventDefault(); // prevent form from submitting normally

  const id = document.getElementById('modal-edit-id').value.trim();
  const name = document.getElementById('modal-edit-name').value.trim();
  const password = document.getElementById('modal-edit-password').value;
  const userId = document.getElementById('modal-edit-user-id').value.trim();
  const role = document.getElementById('modal-edit-role').value.trim();
  const production = document.getElementById('modal-edit-production').value.trim();
  const productionLocation = document.getElementById('modal-edit-production-location').value.trim();

  // Prepare data payload
  const payload = {
    id: id,
    user_id: userId,
    name: name || null,
    password: password || null,
    role: role || null,
    production: production || null,
    production_location: productionLocation || null,
  };

  // Remove null or empty fields
  Object.keys(payload).forEach(key => {
    if (payload[key] === null || payload[key] === '') {
      delete payload[key];
    }
  });

  fetch('/mes/api/controllers/accounts/updateAccount.php', { // your PHP endpoint
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify(payload)
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      Swal.fire({
        icon: 'success',
        title: 'Success',
        text: data.message || 'User updated successfully.',
        confirmButtonText: 'OK'
      }).then(() => {
        // Optionally close modal and refresh UI
        const modalElement = document.getElementById('updateUserModal');
        const modal = bootstrap.Modal.getInstance(modalElement);
        modal.hide();

        // Refresh or reload your user list here, e.g., call your renderTable(users) function
      });
    } else {
      Swal.fire({
        icon: 'error',
        title: 'Update Failed',
        text: data.message || 'An error occurred during update.',
      });
    }
  })
  .catch(error => {
    console.error('Fetch error:', error);
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: 'An unexpected error occurred. Please try again later.',
    });
  });
});
</script>