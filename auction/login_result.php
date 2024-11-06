<?php
// Start session
session_start();

// Check if POST variables exist
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Extract and sanitize user-submitted email and password
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';

    // Check if email or password is empty
    if (empty($email) || empty($password)) {
        echo('<div class="text-center">Email or password cannot be empty. Please try again.</div>');
        exit();
    }

    // Connect to the database
    $file = file_get_contents('data/config.json');
    $config = json_decode($file, true);

    $conn = new mysqli($config["servername"], $config["username"], $config["password"], $config["dbname"]);

    // Check if the connection to the database is successful
    if ($conn->connect_error) {
        die("Connection Failure: " . $conn->connect_error);
    }

    // Query the database to check if the email exists in buyer table
    $sql = "SELECT user_ID, password, 'buyer' AS role FROM buyer WHERE email = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Prepare failed (Buyer): (" . $conn->errno . ") " . $conn->error);
    }
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    // If the user is found in buyer table
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        // Verify the password (in real systems, password hashing should be used)
        if ($user['password'] === $password) {
            // Set session variables
            $_SESSION['logged_in'] = true;
            $_SESSION['username'] = $email;
            $_SESSION['account_type'] = 'buyer';
            echo('<div class="text-center">Welcome, user: ' . htmlspecialchars($email) . '! You will be redirected shortly.</div>');
            // Redirect to index.php after 5 seconds
            header("refresh:5;url=index.php");
            exit();
        } else {
            // Incorrect password
            echo('<div class="text-center">Invalid password. Please try again.</div>');
            exit();
        }
    }

    // If not found in buyer table, check the seller table
    $sql = "SELECT user_ID, password, 'seller' AS role FROM seller WHERE email = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Prepare failed (Seller): (" . $conn->errno . ") " . $conn->error);
    }
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    // If the user is found in seller table
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        // Verify the password
        if ($user['password'] === $password) {
            // Set session variables
            $_SESSION['logged_in'] = true;
            $_SESSION['username'] = $email;
            $_SESSION['account_type'] = 'seller';
            echo('<div class="text-center">Welcome, user: ' . htmlspecialchars($email) . '! You will be redirected shortly.</div>');
            // Redirect to index.php after 5 seconds
            header("refresh:5;url=index.php");
            exit();
        } else {
            // Incorrect password
            echo('<div class="text-center">Invalid password. Please try again.</div>');
            exit();
        }
    }

    // If not found in either table
    echo('<div class="text-center">Email not found. Please try again or register.</div>');

    // Close the database connection
    $stmt->close();
    $conn->close();
} else {
    // If not accessed via POST method
    echo('<div class="text-center">Invalid request method.</div>');
}
?>
