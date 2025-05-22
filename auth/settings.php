<?php session_start(); ?>

<div class="page-content">
  <nav class="page-breadcrumb d-flex justify-content-between align-items-center">
    <ol class="breadcrumb mb-0">
      <li class="breadcrumb-item"><a href="#">Pages</a></li>
      <li class="breadcrumb-item active" aria-current="page">Account Setting</li>
    </ol>
  </nav>

  <div class="row mt-3">
    <div class="col-md-12 grid-margin stretch-card">
      <div class="card shadow-sm">
        <div class="card-body">
          <h6 class="card-title mb-3">Profile</h6>

          <div class="container">
            <ul class="list-group list-group-flush" id="profileFields">
              <?php
              $fields = [
                'user_id' => 'User ID',
                'section' => 'Section',
                'email' => 'Email',
                'department' => 'Department'
              ];

              foreach ($fields as $key => $label):
                  $value = htmlspecialchars($_SESSION[$key] ?? '');
                  $displayValue = $value !== '' ? $value : '<em>None</em>';
              ?>
                <li class="list-group-item d-flex justify-content-between align-items-center" data-field="<?= $key ?>">
                  <div class="field-label"><strong><?= $label ?>:</strong></div>
                  <div class="field-value" style="min-width: 200px;">
                    <span class="value-text"><?= $displayValue ?></span>
                  </div>
                </li>
              <?php endforeach; ?>
            </ul>

            <div class="mt-4 d-flex justify-content-center gap-5">
              <button type="button" class="btn btn-outline-primary mr-3" data-bs-toggle="modal" data-bs-target="#changeEmailModal">
                <i class="bi bi-envelope"></i> Change Email
              </button>
              <button type="button" class="btn btn-outline-warning ml-3" data-bs-toggle="modal" data-bs-target="#changePasswordModal">
                <i class="bi bi-key"></i> Change Password
              </button>
            </div>

          </div>

        </div>
      </div>
    </div>
  </div>
</div>

<!-- Change Email Modal -->
<div class="modal fade" id="changeEmailModal" tabindex="-1" aria-labelledby="changeEmailModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form action="/mes/api/accounts/updateEmail.php" method="POST" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="changeEmailModalLabel">Change Email</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label for="newEmail" class="form-label">New Email Address</label>
          <input type="email" class="form-control" id="newEmail" name="new_email" required value="<?= htmlspecialchars($_SESSION['email'] ?? '') ?>">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" id="email_submit"class="btn btn-primary">Update Email</button>
      </div>
    </form>
  </div>
</div>

<!-- Change Password Modal -->
<div class="modal fade" id="changePasswordModal" tabindex="-1" aria-labelledby="changePasswordModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form action="update_password.php" method="POST" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="changePasswordModalLabel">Change Password</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label for="currentPassword" class="form-label">Current Password</label>
          <input type="password" class="form-control" id="currentPassword" name="current_password" required>
        </div>
        <div class="mb-3">
          <label for="newPassword" class="form-label">New Password</label>
          <input type="password" class="form-control" id="newPassword" name="new_password" required>
        </div>
        <div class="mb-3">
          <label for="confirmPassword" class="form-label">Confirm New Password</label>
          <input type="password" class="form-control" id="confirmPassword" name="confirm_password" required>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-warning">Update Password</button>
      </div>
    </form>
  </div>
</div>

<!-- Bootstrap Icons -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

<!-- Bootstrap JS Bundle (with Popper) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
   document.addEventListener('DOMContentLoaded', function () {
        // document.getElementById('email_submit').addEventListener('click',(event)=>{
        //     event.preventDefault();
        //     const new_email = document.getElementById('newEmail').value;
        //     console.log(new_email);

        // })
    
    



   });

</script>