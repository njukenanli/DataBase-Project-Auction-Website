<?php
require 'email/autoload.php'; // using the composer autoloader

use PHPMailer\PHPMailer\PHPMailer; //import phpmailer
use PHPMailer\PHPMailer\Exception;

// display_time_remaining:
// Helper function to help figure out what time to display
function display_time_remaining($interval) {

    if ($interval->days == 0 && $interval->h == 0) {
      // Less than one hour remaining: print mins + seconds:
      $time_remaining = $interval->format('%im %Ss');
    }
    else if ($interval->days == 0) {
      // Less than one day remaining: print hrs + mins:
      $time_remaining = $interval->format('%hh %im');
    }
    else {
      // At least one day remaining: print days + hrs:
      $time_remaining = $interval->format('%ad %hh');
    }

  return $time_remaining;

}

function print_listing_li($item_id, $title, $desc, $price, $num_bids, $end_time, $image_path) {
  // Truncate long descriptions
  if (strlen($desc) > 250) {
    $desc_shortened = substr($desc, 0, 250) . '...';
  } else {
    $desc_shortened = $desc;
  }

  // Fix language of bid vs. bids
  $bid = ($num_bids == 1) ? ' bid' : ' bids';

  // Calculate time to auction end
  $now = new DateTime();
  $time_remaining = ($now > $end_time) 
    ? 'This auction has ended' 
    : display_time_remaining(date_diff($now, $end_time)) . ' remaining';

  // Print HTML
  echo('<li class="list-group-item d-flex justify-content-between">');
  echo('<div class="p-2 mr-5">');
  if ($image_path && file_exists($image_path)) {
    echo('<img src="' . htmlspecialchars($image_path) . '" alt="Item Image" style="max-width: 100px; max-height: 100px; margin-right: 10px;">');
  } else {
    // 显示默认图片
    // echo('<img src="uploads/default_image.jpg" alt="Default Image" style="max-width: 100px; max-height: 100px; margin-right: 10px;">');
  }
  echo('<h5><a href="listing.php?item_id=' . $item_id . '">' . $title . '</a></h5>' . $desc_shortened);
  echo('</div>');
  echo('<div class="text-center text-nowrap"><span style="font-size: 1.5em">£' . number_format($price, 2) . '</span><br/>' . $num_bids . $bid . '<br/>' . $time_remaining . '</div>');
  echo('</li>');
}

function ConnectDB($con_dir = "data/config.json"){
  $config = json_decode(file_get_contents($con_dir), true);
  $conn = new mysqli($config["servername"], $config["username"], $config["password"], $config["dbname"]);
  if ($conn->connect_error) {
    die("Connection Failure: " . $conn->connect_error);
  } 
  return $conn;
}

function send_email($receiver_email, $receiver_name, $subject, $message, $email_dir = "email/config.json") {
  $mail = new PHPMailer(true);
  $config = json_decode(file_get_contents($email_dir), true);

  try {
      // Server settings
      $mail->isSMTP();
      $mail->Host = $config["host"]; // Gmail SMTP server
      $mail->Port = $config["port"];
      $mail->Username = $config["username"]; // set your email account at email/config.json
      $mail->Password = $config["password"];   // set your email password at email/config.json
      $mail->SMTPAuth = true;
      $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;

      // Recipients
      $mail->setFrom($config["username"], 'Auction Platform'); // sender
      $mail->addAddress($receiver_email, $receiver_name); 

      // Content
      $mail->isHTML(true);
      $mail->Subject = $subject; 
      $mail->Body    = $message; 

      $mail->send();
      //echo "Email sent to: $receiver_email<br>";
  } catch (Exception $e) {
      echo "Email could not be sent. Mailer Error: {$mail->ErrorInfo}<br>";
  }
}
?>
