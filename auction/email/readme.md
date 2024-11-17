# For user: Set-up to enable our email function #
Thanks to York's effort now this program is able to send emails to notify users of events.

S1. Third-party libraries needed have already been put here. Ref: composer website https://getcomposer.org/

S2. Locate the php.ini file at the following path: C:\xampp\php\php.ini
Open the file and use Ctrl + F to search for the following keywords. Modify the parameters as shown below: 
> 
>     [mail function] 
> 
> 	SMTP = smtp.gmail.com 
> 
> 	smtp_port = 587 
> 
> 	sendmail_from = you@example.com -- Set this to your email address 
> 
> 	sendmail_path = "\"C:\xampp\sendmail\sendmail.exe\" -t" -- if your xampp is installed in a diferent directory, change the address "C:\xampp\" here.

S3. Go to your Google account and enable two-step verification. After enabling it, visit https://myaccount.google.com/apppasswords to generate a 16-digit app password. This is important; consider taking a screenshot to save it. Fill your username and app password into aution/email/config.json. 

For easy deployment, an email account has been created for testing, which has been filled into config.json. Do change it to your own account, as this test email will be disabled later.

# For developer: How to semd emails in PHP #
The test_email.php by York in this directory is a demo code to show you how to semd emails in PHP.

We have encapsulate this fuction into utilities.php/send_email($receiver_email, $receiver_name, $subject, $message_body). Developers only need to call this function to send emails.