<?php

// TODO: Extract $_POST variables, check they're OK, and attempt to create
// an account. Notify user of success/failure and redirect/give navigation 
// options.

require_once("utilities.php");

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if POST variables exist and validate them
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Extract and sanitize user-submitted email, password, and account type
    $accountType = isset($_POST['accountType']) ? $_POST['accountType'] : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';
    $passwordConfirmation = isset($_POST['passwordConfirmation']) ? trim($_POST['passwordConfirmation']) : '';

    // Check if any required fields are empty
    if (empty($accountType) || empty($email) || empty($password) || empty($passwordConfirmation)) {
        echo('<div class="text-center">All fields are required. Please try again.</div>');
        header("refresh:5;url=register.php");
        exit();
    }

    // Check if passwords match
    if ($password !== $passwordConfirmation) {
        echo('<div class="text-center">Passwords do not match. Please try again.</div>');
        header("refresh:5;url=register.php");
        exit();
    }

    // Hash the password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Connect to the database
    $conn = ConnectDB();

    // Prepare SQL statement based on account type
    if ($accountType === "buyer") {
        $sql = "INSERT INTO buyer (email, password) VALUES (?, ?)";
    } elseif ($accountType === "seller") {
        $sql = "INSERT INTO seller (email, password) VALUES (?, ?)";
    } else {
        echo('<div class="text-center">Invalid account type. Please try again.</div>');
        header("refresh:5;url=register.php");
        exit();
    }

    // Execute the SQL statement
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Prepare failed: (" . $conn->errno . ") " . $conn->error);
    }

    $stmt->bind_param("ss", $email, $hashedPassword);
    if ($stmt->execute()) {
        echo('<div class="text-center">Account successfully created! You will be redirected to the login page shortly.</div>');
        header("refresh:5;url=index.php");
    } else {
        echo('<div class="text-center">Error creating account. This email may already be registered. Please try again.</div>');
        header("refresh:5;url=register.php");
    }

    $stmt->close();
    $conn->close();
} else {
    // If not accessed via POST method
    echo('<div class="text-center">Invalid request method. You will be redirected shortly.</div>');
    header("refresh:5;url=register.php");
}
?>
