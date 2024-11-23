<?php
session_start();
include_once("header.php");
require("utilities.php");

# Checking user's credential, otherwise swap to welcome page
if (!isset($_SESSION['logged_in']) || $_SESSION['account_type'] != 'buyer') {
    header("Location: browse.php");
    exit();
}

# Get the current user's email
$user_email = $_SESSION['username'];
?>

<div class="container">

<h2 class="my-3">My bids</h2>

<?php
// Connect with the database
$conn = ConnectDB();

// Perform a query to pull up the auctions they've bid on
$sql = "SELECT 
            Item.item_ID, 
            Item.title, 
            Item.description, 
            Item.image_path,
            Item.end_date,
            COUNT(DISTINCT Bid.bid_ID) AS num_bids, -- Calculate total bids for the item
            MAX(CASE WHEN Buyer.email = ? THEN Bid.bid_price ELSE NULL END) AS my_bid_price -- Your bid price
        FROM 
            Item
        LEFT JOIN 
            Bid ON Bid.item_ID = Item.item_ID -- Link all bids
        LEFT JOIN 
            Buyer ON Bid.buyer_ID = Buyer.user_ID -- Link to buyers
        WHERE 
            Bid.item_ID IN (SELECT item_ID FROM Bid WHERE buyer_ID = (SELECT user_ID FROM Buyer WHERE email = ?)) 
        GROUP BY 
            Item.item_ID
        ORDER BY 
            Item.end_date DESC";

// Pre-processing searching results, loop through results
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("ss", $user_email, $user_email); // Bind email twice for the query
    $stmt->execute();
    $result = $stmt->get_result();

    // Print out all the results if there are any
    if ($result->num_rows > 0) {
        echo "<ul class='list-group'>";
        while ($row = $result->fetch_assoc()) {
            $item_id = $row['item_ID'];
            $title = $row['title'];
            $desc = $row['description'];
            $num_bids = $row['num_bids'];
            $price = $row['my_bid_price']; // Your bid price
            $end_time = new DateTime($row['end_date']);
            $image_path = $row['image_path'];

            // Call print list function
            print_listing_li($item_id, $title, $desc, $price, $num_bids, $end_time,$image_path);
        }
        echo "</ul>";
    } else {
        // If there is no result, print out some hints
        echo "<p>You have not bid on anything...</p>";
    }
    // Disconnect from database
    $stmt->close();
} else {
    // If there is an error when querying the database
    echo "<p>Error querying the database.</p>";
}
$conn->close();
?>
</div>

<?php include_once("footer.php") ?>
