<?php
session_start();


unset($_SESSION['logged_in']);
unset($_SESSION['account_type']);


setcookie(session_name(), "", time() - 360, "/");


session_destroy();


$base_url = dirname($_SERVER['PHP_SELF']);


header("Location: $base_url/index.php");
exit; 
?>
