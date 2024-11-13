<?php
include_once("header.php");
require("utilities.php");

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
echo '<h2 class="my-3">My listings</h2>';

// Create a temporary table to store the highest bid and bid count for each auction
$sql = "
    CREATE TEMPORARY TABLE HighestBidPrice AS
    (
        SELECT item_ID, MAX(bid_price) AS highest_bid, COUNT(buyer_ID) AS num_bids 
        FROM Bid 
        GROUP BY item_ID
        UNION
        SELECT Item.item_ID, Item.starting_price AS highest_bid, 0 AS num_bids
        FROM Item 
        WHERE NOT EXISTS (SELECT Bid.item_ID FROM Bid WHERE Bid.item_ID = Item.item_ID)
    )
";
if ($conn->query($sql) === FALSE) {
    die("Execution Failure: " . $conn->error);
}

// Select data from Item and temporary table HighestBidPrice
$query = "
    SELECT i.item_ID, i.description, i.starting_price, i.reserve_price, 
           hbp.num_bids, i.end_date
    FROM Item i
    LEFT JOIN HighestBidPrice hbp ON i.item_ID = hbp.item_ID
    WHERE i.seller_ID = ?
";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Assuming you have a print_listing_li function defined in utilities.php
        print_listing_li($row['item_ID'], $row['description'], $row['starting_price'], $row['reserve_price'], $row['num_bids'], $row['end_date']);
    }
} else {
    echo "You have no listings currently.";
}

// Close the database connection
$conn->close();
echo '</div>';

include_once("footer.php");
?>
