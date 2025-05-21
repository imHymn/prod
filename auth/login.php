<?php
session_start();


if(isset($_SESSION['url_request'])){
  echo  $_SESSION['url_request'];
}


if (isset($_SESSION['user_id'])) {
    $id = $_SESSION['user_id'];
    header("Location: /mes/index.php");
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

    <!-- Vendor css -->
     
    <link href="../lib/login_lib/lib/font-awesome/css/font-awesome.css" rel="stylesheet">
    <link href="../lib/login_lib/lib/Ionicons/css/ionicons.css" rel="stylesheet">

    <!-- Slim CSS -->
    <link rel="stylesheet" href="../lib/login_lib/css/slim.css">

  </head>
  <!-- style="background: radial-gradient(#624dc7, #071b2f);" -->
  <body >

    <div class="signin-wrapper">
        <form method="POST" action="./../api/auth/login.php">
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

                <div class="form-group">
                    <input type="text" name="username" class="form-control" placeholder="Enter your username" required>
                </div><!-- form-group -->
                <div class="form-group mg-b-50">
                    <input type="password" name="password" class="form-control" placeholder="Enter your password" required>
                </div><!-- form-group -->
                <button class="btn btn-primary btn-block btn-signin" type="submit">Sign In</button>
                <div class="text-center ">

<a href="#" id="forgot-password-link" class="d-block">Forgot Password?</a>

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
<script>
  document.addEventListener('DOMContentLoaded', function () {
  

function getCookie(name) {
  const cookies = document.cookie.split(';');
  for(let i = 0; i < cookies.length; i++) {
    let c = cookies[i].trim();
    if(c.indexOf(name + '=') === 0) {
      return c.substring(name.length + 1);
    }
  }
  return null;
}

// Helper function to delete a cookie by name
function deleteCookie(name) {
  document.cookie = name + '=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;';
}

window.addEventListener('pageshow', function(event) {
  let tempValue = getCookie("LogStatus");
  console.log(tempValue);
  
  if(tempValue != null) {
    window.location.href = 'http://10.0.6.5/mes/';
    deleteCookie('LogStatus');
  }
});

    

    const forgotPasswordLink = document.getElementById('forgot-password-link');
    const forgotPasswordModalEl = document.getElementById('forgotPasswordModal');

    // Create Bootstrap Modal instance
    const forgotPasswordModal = new bootstrap.Modal(forgotPasswordModalEl);

    forgotPasswordLink.addEventListener('click', function (e) {
      e.preventDefault();

      // Optional: Load content dynamically before showing modal (using fetch)
      fetch('forgotPassword.php')
        .then(response => response.text())
        .then(html => {
          document.getElementById('forgotPasswordContent').innerHTML = html;
          forgotPasswordModal.show();
        });

      // For now, just show the modal
      forgotPasswordModal.show();
    });
  });
</script>

<script>

window.addEventListener('popstate', function(event) {
    console.log('Back button was pressed');
    // You can trigger any JavaScript code here when going back
});

</script>
    <script src="../lib/login_lib/lib/jquery/js/jquery.js"></script>
    <script src="../lib/login_lib/lib/popper.js/js/popper.js"></script>
    <script src="../lib/login_lib/lib/bootstrap/js/bootstrap.js"></script>

    <script src="../lib/login_lib/js/slim.js"></script>

  </body>
</html>
