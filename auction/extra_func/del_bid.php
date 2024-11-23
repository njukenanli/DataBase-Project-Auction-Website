<?php
require "../utilities.php";  

// Retrieve POST data
if (!isset($_POST['item_id']) || !isset($_POST['buyer_id'])) {
    die("Invalid request. Missing required parameters.");
}

$item_id = intval($_POST['item_id']);
$buyer_id = intval($_POST['buyer_id']);
$conn = ConnectDB("../data/config.json");

// Delete the specified bid
$sql = "DELETE FROM Bid WHERE item_ID = ? AND buyer_ID = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("SQL statement preparation failed: " . $conn->error);
}
$stmt->bind_param("ii", $item_id, $buyer_id);
if (!$stmt->execute()) {
    die("Failed to delete bid: " . $stmt->error);
}
$stmt->close();

// Check if there are any remaining bids
$sql = "SELECT MAX(bid_price) AS highest_bid FROM Bid WHERE item_ID = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("SQL statement preparation failed: " . $conn->error);
}
$stmt->bind_param("i", $item_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$highest_bid = isset($row['highest_bid']) ? $row['highest_bid'] : null;
$stmt->close();

// If there are no remaining bids, set the highest price to the starting price
if ($highest_bid === null) {
    $sql = "SELECT starting_price FROM Item WHERE item_ID = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("SQL statement preparation failed: " . $conn->error);
    }
    $stmt->bind_param("i", $item_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $highest_bid = isset($row['starting_price']) ? $row['starting_price'] : 0;
    $stmt->close();
}

// TODO: Retrieve the email addresses of all buyers watching the item

// TODO: if the bid in the database is the highest bid of this item, send an email to all the buyers in the watchlist about the change of the highest price, and the new highest bid price of this item (could be no bid left, be careful!)

// HINT: send_email($buyer_email, $buyer_name, $title, $content, "../email/config.json");
$conn->close();
echo "bid deleted successfully, redirecting...";
header("refresh:5;url=../../index.php");
