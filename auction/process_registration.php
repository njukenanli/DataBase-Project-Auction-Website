<?php

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

    // Verify that account type is valid
    if ($accountType !== "buyer" && $accountType !== "seller") {
        echo('<div class="text-center">Invalid account type. Please try again.</div>');
        header("refresh:5;url=register.php");
        $conn->close();
        exit();
    }

    // Check if email already exists in the selected account type table
    $checkSql = "SELECT email FROM $accountType WHERE email = ?";
    $checkStmt = $conn->prepare($checkSql);
    if (!$checkStmt) {
        die("Prepare failed: (" . $conn->errno . ") " . $conn->error);
    }

    $checkStmt->bind_param("s", $email);
    $checkStmt->execute();
    $checkStmt->store_result();

    // If email exists, display error message and stop execution
    if ($checkStmt->num_rows > 0) {
        echo('<div class="text-center">This email is already registered. Please use a different email.</div>');
        header("refresh:5;url=register.php");
        $checkStmt->close();
        $conn->close();
        exit();
    }
    $checkStmt->close();

    // Prepare SQL statement based on account type
    $sql = "INSERT INTO $accountType (email, password) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Prepare failed: (" . $conn->errno . ") " . $conn->error);
    }

    $stmt->bind_param("ss", $email, $hashedPassword);
    if ($stmt->execute()) {
        echo('<div class="text-center">Account successfully created! You will be redirected to the login page shortly.</div>');
        header("refresh:5;url=index.php");
    } else {
        echo('<div class="text-center">Error creating account. Please try again.</div>');
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
