<?php
require "../utilities.php";  

// Get POST data
if (!isset($_POST['item_id'])) {
    die("Invalid request. Missing required parameters.");
}
$item_id = intval($_POST['item_id']);
$conn = ConnectDB("../data/config.json");

// Get all buyer emails who are watching the item
$sql = "SELECT Buyer.email 
            FROM Watch 
            JOIN Buyer ON Watch.buyer_ID = Buyer.user_ID 
            WHERE Watch.item_ID = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("SQL preparation failed: " . $conn->error);
}
$stmt->bind_param("i", $item_id);
$stmt->execute();
$result = $stmt->get_result();

// Notify all buyers watching the item
$subject = "Auction item #$item_id has been cancelled";
$message = "We regret to inform you that auction item #$item_id has been cancelled.";
while ($row = $result->fetch_assoc()) {
    $email = $row['email'];
     // Use the send_email function in utilities.php to send notifications
    send_email($email, "Valued Buyer", $subject, $message, '../email/config.json');
}
$stmt->close();

$sql = "DELETE FROM Bid WHERE item_ID = ?";
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
$sql = "DELETE FROM Watch WHERE item_ID = ?";
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
$sql = "DELETE FROM Item WHERE item_ID = ?";
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
