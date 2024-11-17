<?php
require 'vendor/autoload.php'; // using the composer autoloader

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

// print_listing_li:
// This function prints an HTML <li> element containing an auction listing
function print_listing_li($item_id, $title, $desc, $price, $num_bids, $end_time)
{
  // Truncate long descriptions
  if (strlen($desc) > 250) {
    $desc_shortened = substr($desc, 0, 250) . '...';
  }
  else {
    $desc_shortened = $desc;
  }
  
  // Fix language of bid vs. bids
  if ($num_bids == 1) {
    $bid = ' bid';
  }
  else {
    $bid = ' bids';
  }
  
  // Calculate time to auction end
  $now = new DateTime();
  if ($now > $end_time) {
    $time_remaining = 'This auction has ended';
  }
  else {
    // Get interval:
    $time_to_end = date_diff($now, $end_time);
    $time_remaining = display_time_remaining($time_to_end) . ' remaining';
  }
  
  // Print HTML
  echo('
    <li class="list-group-item d-flex justify-content-between">
    <div class="p-2 mr-5"><h5><a href="listing.php?item_id=' . $item_id . '">' . $title . '</a></h5>' . $desc_shortened . '</div>
    <div class="text-center text-nowrap"><span style="font-size: 1.5em">£' . number_format($price, 2) . '</span><br/>' . $num_bids . $bid . '<br/>' . $time_remaining . '</div>
  </li>'
  );
}

function ConnectDB($con_dir = "data/config.json"){
  $config = json_decode(file_get_contents($con_dir), true);
  $conn = new mysqli($config["servername"], $config["username"], $config["password"], $config["dbname"]);
  if ($conn->connect_error) {
    die("Connection Failure: " . $conn->connect_error);
  } 
  return $conn;
}

function send_email($receiver_email, $receiver_name, $subject, $message_body) {
  $mail = new PHPMailer(true);

  try {
      // Server settings
      $mail->isSMTP();
      $mail->Host = 'smtp.gmail.com'; // Gmail SMTP server
      $mail->SMTPAuth = true;
      $mail->Username = 'yorkeadgbe@gmail.com'; // 替換為你的 Gmail 地址
      $mail->Password = '16 digtis password';   // 替換為 Gmail 應用程式密碼
      $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
      $mail->Port = 587;

      // Recipients
      $mail->setFrom('yorkeadgbe@gmail.com', 'Auction Platform'); // 固定發件人
      $mail->addAddress($receiver_email, $receiver_name); 

      // Content
      $mail->isHTML(true);
      $mail->Subject = $subject; 
      $mail->Body    = $message_body; 

      $mail->send();
      echo "Email sent to: $receiver_email<br>";
  } catch (Exception $e) {
      echo "Email could not be sent. Mailer Error: {$mail->ErrorInfo}<br>";
  }
}
?>