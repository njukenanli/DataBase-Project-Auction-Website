<?php
include_once("header.php");
require("utilities.php");

function shorten($str) {
    if (strlen($str) > 250) {
        $desc_shortened = substr($str, 0, 250) . '...';
    } else {
        $desc_shortened = $str;
    }
    return $desc_shortened;
}

function print_listing($enquiry_id, $title, $desc, $enquiry, $answer) {
    echo('<li class="list-group-item justify-content-between">');
    echo('<h5><a href="extra_func/seller_enquiry.php?enquiry_id=' . $enquiry_id . '">' . $title . '</a></h5>');
    echo ('<p style="text-align: left;">Description: ' . shorten($desc) . '</p>');
    echo ('<p style="text-align: left;">Enquiry: ' . shorten($enquiry) . '</p>');
    echo ('<p style="text-align: left;">Answer: ' . shorten($answer) . '</p>');
    echo('</li>');
}

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    die("Please log in to view your listings.");
}

// Connect to the database
$conn = ConnectDB();

// Get the user_id from the Seller table based on the username stored in session
$username = $_SESSION['username'];
$query = "SELECT user_ID FROM Seller WHERE email = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$user_id = $user['user_ID'] ?? null;

if (!$user_id) {
    die("User ID not found.");
}

echo '<div class="container my-5">';
echo '<h2 class="my-3">My Enquiry</h2>';

// Select data from Item and temporary table HighestBidPrice
$sql = "SELECT Enquiry.enquiry_ID, Item.title, Item.description, Enquiry.enquiry, Enquiry.answer     
            FROM Item, Enquiry WHERE Item.item_ID = Enquiry.item_ID AND Item.seller_ID = $user_id    
            ORDER BY  enquiry_time DESC ";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        print_listing($row['enquiry_ID'], $row["title"], $row['description'], $row['enquiry'], $row['answer']);
    }
} else {
    echo "<br>You have no enquiries currently.<br>";
}

// Close the database connection
$conn->close();
echo '</div>';

include_once("footer.php");
?>
