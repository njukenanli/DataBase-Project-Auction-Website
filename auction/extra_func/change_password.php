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
  # TODO: generate a random code and send it to the email adress.
  # hint: send_email($email, $buyer_name, $subject, $message, '../email/config.json');
  return code;
}

function email_in_database($email) {
  # TODO: use SQL to check whether the email is in registered in the buyer or seller.
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
    if (isset($_POST["send_code"]) and !empty($_POST["send_code"])) {
        $email = $_POST["email"];
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $EmailErr = "Invalid email format";
        } 
        elseif (!email_in_database($email)) {
            $EmailErr = "Email not Registered!";
        }
        else {
            $code = send_code($email); // Generate and send the code
            $code_sent = "Verification code sent to $email";
        }
    }
    if (isset($_POST["submit"])) {
        if (isset($_POST["code"]) and $_POST["code"] === $code) {
          if (isset($_POST["password"]) and isset($_POST["password_repeat"]) and $_POST["password"] === $_POST["password_repeat"]) {
              #TODO: use SQL to alter the password in buyer and/or seller table.
              $conn = ConnectDB("../data/config.json");
              $conn->close();
              $success = "password changed successfully! please log in again! redirecting...";
              session_start();
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