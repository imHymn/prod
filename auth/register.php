<?php
session_start();

// If the user is already logged in, redirect to dashboard or home
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Display error or success messages if any
if (isset($_SESSION['error_message'])) {
    $error_message = $_SESSION['error_message'];
    unset($_SESSION['error_message']);
}

if (isset($_SESSION['success_message'])) {
    $success_message = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>Register - Roberts Prod</title>

  <link href="../lib/login_lib/lib/font-awesome/css/font-awesome.css" rel="stylesheet">
  <link href="../lib/login_lib/lib/Ionicons/css/ionicons.css" rel="stylesheet">
  <link rel="stylesheet" href="../lib/login_lib/css/slim.css">
</head>
<body>

  <div class="signin-wrapper">
    <form method="POST" action="./../api/auth/register.php">
  <div class="signin-box">
    <h2 class="slim-logo"><a href="index.php">Roberts Production System<span>.</span></a></h2>
    <h2 class="signin-title-primary">Create an account</h2>
    <h3 class="signin-title-secondary">Sign up to get started.</h3>

    <?php if (isset($error_message)): ?>
      <div class="alert alert-danger">
        <?php echo $error_message; ?>
      </div>
    <?php endif; ?>

    <?php if (isset($success_message)): ?>
      <div class="alert alert-success">
        <?php echo $success_message; ?>
      </div>
    <?php endif; ?>

    <div class="form-group">
      <input type="text" name="username" class="form-control" placeholder="User ID" required>
    </div>

    <div class="form-group">
      <input type="email" name="email" class="form-control" placeholder="Email address" required>
    </div>

    <div class="form-group mg-b-50">
      <input type="password" name="password" class="form-control" placeholder="Password" required>
    </div>

    <button class="btn btn-primary btn-block btn-signin" type="submit">Register</button>

    <div class="text-center mt-3">
      <a href="/mes/auth/login" class="d-block">Already have an account? Sign In</a>
    </div>
  </div>
</form>

  </div>

  <script src="../lib/login_lib/lib/jquery/js/jquery.js"></script>
  <script src="../lib/login_lib/lib/popper.js/js/popper.js"></script>
  <script src="../lib/login_lib/lib/bootstrap/js/bootstrap.js"></script>
  <script src="../lib/login_lib/js/slim.js"></script>
  
</body>
</html>
