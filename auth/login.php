<?php
session_start();

if (isset($_SESSION['user_id'])) {
  $id = $_SESSION['user_id'];
  header("Location: /mes/index.php?page_active=accounts");
  exit();
}


if (isset($_SESSION['error_message'])) {
  $error_message = $_SESSION['error_message'];
  unset($_SESSION['error_message']); // Clear the message after displaying it
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <!-- Required meta tags -->
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

  <title>Roberts Prod</title>


  <link rel="stylesheet" href="../assets/css/slim.css">

</head>
<!-- style="background: radial-gradient(#624dc7, #071b2f);" -->

<body>

  <div class="signin-wrapper">
    <form method="POST" action="/mes/api/accounts/login.php">
      <div class="signin-box">
        <!-- <img src="assets/images/roberts2.png" alt="roberts" width="260px;" style="padding-bottom: 10px;"> -->
        <h2 class="slim-logo"><a href="index.php">Roberts Production System<span>.</span></a></h2>
        <h2 class="signin-title-primary">Welcome back!</h2>

        <h3 class="signin-title-secondary">Sign in to continue.</h3>

        <!-- Error Message Display -->
        <?php if (isset($error_message)): ?>
          <div class="alert alert-danger">
            <?php echo $error_message; ?>
          </div>
        <?php endif; ?>
        <div id="loginError" class="alert alert-danger" style="display:none;"></div>

        <div class="form-group">
          <input type="text" name="user_id" class="form-control" placeholder="Enter your username" required>
        </div><!-- form-group -->
        <div class="form-group mg-b-50">
          <input type="password" name="password" class="form-control" placeholder="Enter your password" required>
        </div><!-- form-group -->
        <button class="btn btn-primary btn-block btn-signin" type="submit">Sign In</button>
        <div class="text-center ">


        </div>
      </div><!-- signin-box -->
    </form>

  </div>
  <div class="modal fade" id="forgotPasswordModal" tabindex="-1" role="dialog" aria-labelledby="forgotPasswordLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="forgotPasswordLabel">Forgot Password</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body" id="forgotPasswordContent">
          <!-- AJAX content will be loaded here -->
        </div>
      </div>
    </div>
  </div>

</body>
<script>
  document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('.signin-wrapper form');
    const errorBox = document.getElementById('loginError');

    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      errorBox.style.display = 'none'; // hide any previous error

      const data = new FormData(form);

      try {
        const res = await fetch('/mes/api/accounts/login.php', {
          method: 'POST',
          body: data,
          credentials: 'include'
        });
        const json = await res.json();

        if (!res.ok || !json.success) {
          showError(json.message || 'Login failed');
          return;
        }

        window.location.replace(`/mes/index.php?page_active=${encodeURIComponent(json.page_active)}`);

      } catch (err) {
        showError('Network error – please try again.');
      }
    });

    function showError(msg) {
      errorBox.textContent = msg;
      errorBox.style.display = 'block';
    }
  });
</script>