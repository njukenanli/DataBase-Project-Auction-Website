<html>
<head>
<style>
.error {color: #FF0000;}
</style>
</head>
<body> 

<?php
require_once("../utilities.php");

function send_code($email) {
  # DONE: generate a random code and send it to the email adress.
  # hint: send_email($email, $buyer_name, $subject, $message, '../email/config.json');
  $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
  $code = '';
  for($i = 0; $i < 4; $i++){
    $code .= $characters[rand(0, strlen( $characters ) -1)];
  }

  $subject = "Your security code is: $code";
  $message = "Your security code is: $code This is a security code. \nPlease don't disclose it to anyone!";
  send_email($email, "Users changing password", $subject, $message, '../email/config.json');

  return $code;
}

function email_in_database($email) {
  # DONE: use SQL to check whether the email is in registered in the buyer or seller.
  $conn = ConnectDB("../data/config.json");
  $sql = "SELECT email FROM Buyer WHERE email = ?
          UNION
          SELECT email FROM Seller WHERE email = ?";
  
  if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("ss", $email, $email);
    if(!$stmt->execute()){
      die("Execution error: " . $stmt->error);
      $stmt->close();
      $conn->close();
      return false;
    }

    $result = $stmt->get_result();
    $exist = $result->num_rows > 0;
    $stmt->close();
    $conn->close();
    return $exist;

  }

  $conn->close();
  return false;
}

$EmailErr = "";
$code_sent = "";
$codeErr = "";
$passwordErr = "";

session_start();
if (isset($_SESSION['logged_in']) and $_SESSION['logged_in']){
  $old_email = $_SESSION['username'];
}
else{
  $old_email = "";
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["send_code"])) {
      $email = $_POST["email"];

        if (empty($_POST['email'])){
          $EmailErr = "Email is required!";
        } 
        elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)){
            $EmailErr = "Invalid email format";
        } 
        elseif (!email_in_database($email)) {
            $EmailErr = "Email not Registered!";
        }
        else {
            $code = send_code($email); // Generate and send the code
            $_SESSION['code'] = $code;
            $code_sent = "Verification code sent to $email";
        }
    }

    if (isset($_POST["submit"])) {
        if (isset($_POST["code"]) and $_POST["code"] === $_SESSION['code']) {
          if (isset($_POST["password"]) and isset($_POST["password_repeat"]) and $_POST["password"] === $_POST["password_repeat"]) {
              #DONE: use SQL to alter the password in buyer and/or seller table.
              $password = $_POST["password"];
              $hashed_password = password_hash($password, PASSWORD_DEFAULT);

              $conn = ConnectDB("../data/config.json");

              $sql1 = "UPDATE Buyer SET password = ? WHERE email = ?";
              $sql2 = "UPDATE Seller SET password = ? WHERE email = ?";

              if ($stmt = $conn->prepare($sql1)) {
                $stmt->bind_param("ss", $hashed_password, $old_email);
                $stmt->execute();
                $stmt->close();
              }

              if ($stmt = $conn->prepare($sql2)) {
              $stmt->bind_param("ss", $hashed_password, $old_email);
              $stmt->execute();
              $stmt->close();
              }

              $conn->close();

              $success = "password changed successfully! please log in again! redirecting...";
              echo $success;
              //Let users relog in or log in using new password
              if (!isset($_SESSION['logged_in']) || (!$_SESSION['logged_in'])) {
                header("refresh:5;url= ../../index.php");
              }
              else {
                header("refresh:5;url= ../logout.php");
              }
          }
          else {
            $passwordErr = "password does not match.";
          }
        }
        else {
          $codeErr = "verification code is wrong.";
        }
    }
} 
?>

<!--TODO: create a form to let a user enter the new email address they use to substitute the old one.-->
<form method="post" action="change_password.php"> 
   user email: <input type="text" name="email" value = <?php echo $old_email;?>>
   <span class="error"><?php echo $EmailErr ?></span>
   <br><br>
   new password: <input type="text" name="password">
   <br><br>
   repeat password: <input type="text" name="password_repeat">
   <span class="error"><?php echo $passwordErr ?></span>
   <br><br>
   verification code: <input type="text" name="code">
   <span class="error"><?php echo $codeErr?></span>
   <span><?php echo $code_sent?></span>
   <button type="submit" name="send_code">Send Verification Code</button>
   <br><br>
   <input type="submit" name="submit" value="submit"> 
</form>

</body>

<button class="btn btn-primary" onclick="window.location.href='../../index.php';">BackToIndex</button>

</html>
