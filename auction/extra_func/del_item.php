<?php
require "../utilities.php";  

// Get POST data
if (!isset($_POST['item_id'])) {
    die("Invalid request. Missing required parameters.");
}

$item_id = intval($_POST['item_id']);
$conn = ConnectDB("../data/config.json");

// Get the seller's email and item title
$sql = "SELECT seller.email, item.title 
        FROM item 
        JOIN seller ON item.seller_ID = seller.user_ID 
        WHERE item.item_ID = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("SQL preparation failed: " . $conn->error);
}
$stmt->bind_param("i", $item_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $row = $result->fetch_assoc();
    $seller_email = $row['email'];
    $item_title = $row['title'];
} else {
    die("Seller or item not found for the given item.");
}
$stmt->close();

// Notify the seller
$subject_seller = "You have cancelled auction item #$item_id - $item_title";
$message_seller = "Dear Seller,\n\nYou have successfully cancelled the auction item \"$item_title\" (ID: #$item_id). 
All associated bids and watchlist entries have been removed.\n\nRegards,\nAuction Platform Team";
send_email($seller_email, "Auction Seller", $subject_seller, $message_seller, '../email/config.json');

// Get all buyer emails who are watching the item
$sql = "SELECT buyer.email 
        FROM watch 
        JOIN buyer ON watch.buyer_ID = buyer.user_ID 
        WHERE watch.item_ID = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("SQL preparation failed: " . $conn->error);
}
$stmt->bind_param("i", $item_id);
$stmt->execute();
$result = $stmt->get_result();

// Notify all buyers watching the item
$subject_buyers = "Auction item #$item_id - $item_title has been cancelled";
$message_buyers = "We regret to inform you that the auction item \"$item_title\" (ID: #$item_id) has been cancelled by the seller.";
while ($row = $result->fetch_assoc()) {
    $buyer_email = $row['email'];
    send_email($buyer_email, "Valued Buyer", $subject_buyers, $message_buyers, '../email/config.json');
}
$stmt->close();

// Delete all bids related to the item
$sql = "DELETE FROM bid WHERE item_ID = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("SQL preparation failed: " . $conn->error);
}
$stmt->bind_param("i", $item_id);
if (!$stmt->execute()) {
    die("Failed to delete bids: " . $stmt->error);
}
$stmt->close();

// Delete all watch list records related to the item
$sql = "DELETE FROM watch WHERE item_ID = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("SQL preparation failed: " . $conn->error);
}
$stmt->bind_param("i", $item_id);
if (!$stmt->execute()) {
    die("Failed to delete watch list entries: " . $stmt->error);
}
$stmt->close();

// Delete the item
$sql = "DELETE FROM item WHERE item_ID = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("SQL preparation failed: " . $conn->error);
}
$stmt->bind_param("i", $item_id);
if (!$stmt->execute()) {
    die("Failed to delete item: " . $stmt->error);
}
$stmt->close();

$conn->close();

echo "Item and related records deleted successfully. Redirecting...";
header("refresh:5;url=../../index.php");
?>
