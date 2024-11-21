<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['logged_in'])) {
    $_SESSION['logged_in'] = false; 
    $_SESSION['username'] = '';
    $_SESSION['account_type'] = ''; 
}
?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  
  <!-- Bootstrap and FontAwesome CSS -->
  <link rel="stylesheet" href="css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

  <!-- Custom CSS file -->
  <link rel="stylesheet" href="css/custom.css">

  <title>[My Auction Site] <!--CHANGEME!--></title>
</head>

<body>

<!-- Navbars -->
<nav class="navbar navbar-expand-lg navbar-light bg-light mx-2">
  <a class="navbar-brand" href="#">Mock Auction Website by Group 25, Database Project</a>
  <ul class="navbar-nav ml-auto">
    <li class="nav-item d-flex align-items-center">
    
<?php
  // 根據當前 session 狀態顯示 Login 或 Logout 按鈕
  if ($_SESSION['logged_in'] == true) {
    echo '<p class="nav-link mb-0">Hello, ' . htmlspecialchars($_SESSION['username']) . ' | </p>';
    echo '<a class="nav-link" href="logout.php">Logout</a>';
    echo '<a class="nav-link" href="extra_func/change_email.php">ChangeEmail</a>';
    echo '<a class="nav-link" href="extra_func/change_password.php">ChangePassword</a>';
  } else {
    echo '<p class="nav-link mb-0">Welcome, guest | </p>';
    echo '<button type="button" class="btn btn-primary ml-2" data-toggle="modal" data-target="#loginModal">Login</button>';
    // 添加注册按钮，与登录按钮样式保持一致
    echo '<a href="register.php" class="btn btn-primary ml-2">Register</a>';
  }
?>

    </li>
  </ul>
</nav>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <ul class="navbar-nav align-middle">
    <li class="nav-item mx-1">
      <a class="nav-link" href="browse.php">Browse</a>
    </li>
<?php
  // 根據賬戶類型顯示不同的導航選項
  if ($_SESSION['account_type'] == 'buyer') {
    echo('
    <li class="nav-item mx-1">
      <a class="nav-link" href="mybids.php">My Bids</a>
    </li>
    <li class="nav-item mx-1">
      <a class="nav-link" href="recommendations.php">Recommended</a>
    </li>');
  }
  if ($_SESSION['account_type'] == 'seller') {
    echo('
    <li class="nav-item mx-1">
      <a class="nav-link" href="mylistings.php">My Listings</a>
    </li>
    <li class="nav-item ml-3">
      <a class="nav-link btn border-light" href="create_auction.php">+ Create auction</a>
    </li>');
  }
?>
  </ul>
</nav>

<!-- Login modal -->
<div class="modal fade" id="loginModal">
  <div class="modal-dialog">
    <div class="modal-content">

      <!-- Modal Header -->
      <div class="modal-header">
        <h4 class="modal-title">Login</h4>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>

      <!-- Modal body -->
      <div class="modal-body">
        <form method="POST" action="login_result.php">
          <div class="form-group">
            <label for="role">Role</label>
            <select class="form-control" id="role" name="role">
              <option selected value="buyer">Buyer</option>
              <option value="seller">Seller</option>
            </select>
          </div>
          <div class="form-group">
            <label for="email">Email</label>
            <input type="text" class="form-control" id="email" name="email" placeholder="Email" required>
          </div>
          <div class="form-group">
            <label for="password">Password</label>
            <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
          </div>
          <button type="submit" class="btn btn-primary form-control">Sign in</button>
        </form>
        <div class="text-center">or <a href="register.php">create an account</a></div>
        <div class="text-center">or <a href="extra_func/change_password.php">forget password?</a></div>
      </div>

    </div>
  </div>
</div> <!-- End modal -->

<!-- Bootstrap and jQuery JavaScript -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
