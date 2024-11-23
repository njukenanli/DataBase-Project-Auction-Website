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

session_start();
if (! isset($_SESSION['username'])){
  die("No user information found! This function should only work after logging in...");
  header("refresh:5;url=../../index.php");
  exit();
}
$old_email = $_SESSION['username'];


$newEmailErr = "";
$code_sent = "";
$codeErr = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
// note that this file can only be visited after the user has logged in. 
// so no need to check wehther the email has been registered.
    if (isset($_POST["send_code"])) {
        $newEmail = $_POST["new_email"];
        if (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
            $newEmailErr = "Invalid email format";
        } else {
            $code = send_code($newEmail); // Generate and send the code
            $code_sent = "Verification code sent to $newEmail";
        }
    }
    if (isset($_POST["submit"])) {
        if (isset($_POST["code"]) and $_POST["code"] === $code) {
          #TODO: use session to get current email. use SQL to alter the user email in the buyer and/or seller table.
          $conn = ConnectDB("../data/config.json");
          $conn->close();
          $success = "email changed successfully! please log in again! redirecting...";
          header("refresh:5;url=../logout.php");
        }
        else {
          $codeErr = "verification code is wrong.";
        }
    }
} 
?>

<!--TODO: create a form to let a user enter the new email address they use to substitute the old one.-->
<form method="post" action="change_email.php"> 
   current email: <?php echo $old_email;?>
   <br><br>
   new email: <input type="text" name="new_email">
   <span class="error"><?php echo $newEmailErr ?></span>
   <br><br>
   verification code: <input type="text" name="code">
   <span class="error"><?php echo $codeErr?></span>
   <span><?php echo $code_sent?></span>
   <button type="submit" name="send_code">Send Verification Code</button>
   <br><br>
   <input type="submit" name="submit" value="Submit"> 
</form>

</body>

<button class="btn btn-primary" onclick="window.location.href='../../index.php';">BackToIndex</button>

</html>
