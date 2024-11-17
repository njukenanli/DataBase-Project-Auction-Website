<?php
require 'autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);

try {
    // Server settings
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'yorkeadgbe@gmail.com'; // 替換為你的 Gmail 地址
    $mail->Password = '16  digits ';       // 輸入你的16位應用程式密碼
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    // Recipients
    $mail->setFrom('yorkeadgbe@gmail.com', 'Your Name');  //輸入一樣的你要發出郵件的gmail 地址
    $mail->addAddress('yyisyork@gmail.com', 'Receiver Name');//接收郵件人的地址

    // Content
    $mail->isHTML(true);
    $mail->Subject = 'Test Email';
    $mail->Body    = 'This is a test email sent using PHPMailer.';

    $mail->send();
    echo 'Message has been sent';
} catch (Exception $e) {
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
}
?>
