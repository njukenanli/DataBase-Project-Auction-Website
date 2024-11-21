<?php
include_once("header.php");
require("utilities.php");

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

echo '<div class="container my-5">';

// Check if the user has seller privileges
if (!isset($_SESSION['account_type']) || $_SESSION['account_type'] !== 'seller') {
    die("Only sellers can create auctions.");
}

// Connect to database
$conn = ConnectDB();

// Extract form data
$title = $_POST['auctionTitle'] ?? '';
$details = $_POST['auctionDetails'] ?? '';
$category_name = $_POST['auctionCategory'] ?? '';
$starting_price = $_POST['auctionStartPrice'] ?? 0;
$reserve_price = $_POST['auctionReservePrice'] ?? 0;
$end_date = $_POST['auctionEndDate'] ?? '';

// Check required fields
if (empty($title) || empty($category_name) || empty($starting_price) || empty($end_date)) {
    die("Please fill in all required fields.");
}

// Handle image upload
$image_path = null; // Default to null in case no image is uploaded
if (isset($_FILES['auctionImage']) && $_FILES['auctionImage']['error'] === UPLOAD_ERR_OK) {
    $upload_dir = "uploads/"; // Directory where images will be saved
    $file_ext = pathinfo($_FILES['auctionImage']['name'], PATHINFO_EXTENSION); // Get file extension
    $unique_file_name = uniqid("auction_", true) . '.' . $file_ext; // Generate unique file name
    $target_file = $upload_dir . $unique_file_name;

    // Create uploads directory if it doesn't exist
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    // Move the uploaded file
    if (move_uploaded_file($_FILES['auctionImage']['tmp_name'], $target_file)) {
        $image_path = $target_file; // Store the file path for database insertion
    } else {
        die("Error uploading the image.");
    }
}

// Get category_ID
$category_query = "SELECT category_ID FROM Category WHERE name = ?";
$stmt = $conn->prepare($category_query);
$stmt->bind_param("s", $category_name);
$stmt->execute();
$result = $stmt->get_result();
$category_id = $result->fetch_assoc()['category_ID'] ?? null;

if (!$category_id) {
    die("Invalid category selected.");
}

// Get user ID from session based on username
$username = $_SESSION['username'];
$query = "SELECT user_ID FROM Seller WHERE email = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$seller_id = $user['user_ID'] ?? null;

if (!$seller_id) {
    die("User ID not found.");
}

// Insert new auction record, including image_path
$insert_query = "
    INSERT INTO Item (title, description, seller_ID, category_ID, starting_price, reserve_price, end_date, image_path) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
";
$stmt = $conn->prepare($insert_query);
$stmt->bind_param("ssiiddss", $title, $details, $seller_id, $category_id, $starting_price, $reserve_price, $end_date, $image_path);

if ($stmt->execute()) {
    echo '<div class="text-center">Auction successfully created! <a href="mylistings.php">View your new listing.</a></div>';
} else {
    echo "Error creating auction: " . $stmt->error;
}

// Close database connection
$conn->close();
echo '</div>';

include_once("footer.php");
?>

