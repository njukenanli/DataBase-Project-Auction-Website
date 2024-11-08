<?php
// Start session
require_once("utilities.php");
session_start();

// Check if POST variables exist
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if email, password or role is empty
    if (!(isset($_POST['email']) and isset($_POST['password']) and isset($_POST['role']))) {
        echo('<div class="text-center">Email, password or role cannot be empty. Please try again.</div>');
        header("refresh:5;url=index.php");
    }
    // Extract and sanitize user-submitted email and password
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $role = $_POST['role'];

    // Connect to the database
    $conn = ConnectDB();
    if ($role === "buyer"){
        // Query the database to check if the email exists in buyer table
        $sql = "SELECT user_ID, password FROM buyer WHERE email = '".$email."'";
        $result = $conn->query($sql);

        // If the user is found in buyer table
        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            // Verify the password (in real systems, password hashing should be used)
            if (password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION['logged_in'] = true;
                $_SESSION['username'] = $email;
                $_SESSION['account_type'] = 'buyer';
                echo('<div class="text-center">Welcome, user: ' . htmlspecialchars($email) . '! You will be redirected shortly.</div>');
                // Redirect to index.php after 5 seconds
                header("refresh:5;url=index.php");
            } else {
                // Incorrect password
                echo('<div class="text-center">Invalid password. Please try again. You will be redirected shortly.</div>');
                header("refresh:5;url=index.php");
            }
        }
        else {
            echo('<div class="text-center">Buyer not found. Please try again or register. You will be redirected shortly.</div>');
            header("refresh:5;url=index.php");
        }
    }
    elseif ($role === "seller") {
        $sql = "SELECT user_ID, password FROM seller WHERE email = '".$email."'";
        $result = $conn->query($sql);

        // If the user is found in seller table
        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            // Verify the password
            if (password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION['logged_in'] = true;
                $_SESSION['username'] = $email;
                $_SESSION['account_type'] = 'seller';
                echo('<div class="text-center">Welcome, user: ' . htmlspecialchars($email) . '! You will be redirected shortly.</div>');
                // Redirect to index.php after 5 seconds
                header("refresh:5;url=index.php");
            } else {
                // Incorrect password
                echo('<div class="text-center">Invalid password. Please try again. You will be redirected shortly.</div>');
                header("refresh:5;url=index.php");
            }
        }
        else {
            echo('<div class="text-center">Seller not found. Please try again or register. You will be redirected shortly.</div>');
            header("refresh:5;url=index.php");
        }
    }
    else{
        echo('<div class="text-center">Wrong role type. Please try again. You will be redirected shortly.</div>');
        header("refresh:5;url=index.php");
    }

    $conn->close();
} else {
    // If not accessed via POST method
    echo('<div class="text-center">Invalid request method. You will be redirected shortly.</div>');
    header("refresh:5;url=index.php");
}
?>
