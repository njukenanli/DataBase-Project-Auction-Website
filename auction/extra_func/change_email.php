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
  $message = "Your security code is: $code\nThis is a security code. Please don't disclose it to anyone!";
  send_email($email, "Users changing email", $subject, $message, '../email/config.json');

  return $code;
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
        if(empty($_POST["new_email"])){
           $newEmailErr = "Email is required!";
        }elseif (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
            $newEmailErr = "Invalid email format";
        } else {
            $code = send_code($newEmail); // Generate and send the code
            $_SESSION['code'] = $code;
            $_SESSION['new_email'] = $newEmail;
            $code_sent = "Verification code sent to $newEmail";
        }
    }
    if (isset($_POST["submit"])) {
        if (isset($_POST["code"]) and $_POST["code"] === $_SESSION['code']) {
          #DONE: use session to get current email. use SQL to alter the user email in the buyer and/or seller table.
          $user_email = $_SESSION['username'];
          $newEmail = $_SESSION['new_email'];

          //Updating the user email
          $conn = ConnectDB("../data/config.json");
          $sql = "UPDATE Buyer SET email = ? WHERE email = ?;
                  UPDATE Seller SET email = ? WHERE email = ?";
          if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ssss", $newEmail, $user_email, $newEmail, $user_email);
            $stmt->execute();
            $stmt->close();
          } else {
            die ("Error querying the database: " . $stmt->error);
          }
          $conn->close();
          $success = "email changed successfully! please log in again! redirecting...";
          echo $success;
          header("refresh:5;url=../logout.php");
          exit();
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
