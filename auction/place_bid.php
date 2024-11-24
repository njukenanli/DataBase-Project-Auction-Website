<?php
include_once("header.php");
require_once("utilities.php");

// DONE: Extract $_POST variables, check they're OK, and attempt to make a bid.
// Notify user of success/failure and redirect/give navigation options.
// Can limit valid bids only to those that can outbid the current highest one, otherwise return failure. 
// DONE: Check whether the current bid outbids somebody and send emails to those outbidded.
// DONE: Send emails to those who are in the watchlist and are watching this item about this new bid.
// DONE: After making a bid, add the buyer to watchlist directly.
//Sending email function: email($receiver, $email, $title, $message){}

// Ensure the user is logged in as a buyer
if (!isset($_SESSION['logged_in']) || (!$_SESSION['logged_in']) || $_SESSION['account_type'] != 'buyer') {
    echo "Please log in as a buyer first";
    header("refresh:5;url=../index.php");
    exit();
}

// Connect to the database
$conn = ConnectDB();
if ($conn->connect_error) {
    echo("Database connection failed: " . $conn->connect_error);
    header("refresh:5; url=../index.php");
    exit();
}

// Get user details from the session
$user_email = $_SESSION['username'];
$sql = "SELECT user_ID FROM Buyer WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $user_email);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$user_id = $row['user_ID'];

// Check if item_id and bid are set in POST request
if (isset($_POST['item_id']) && isset($_POST['bid'])) {
    $item_id = $_POST['item_id'];
    $bid = floatval($_POST['bid']);

    // Validate that the bid amount is greater than zero
    if ($bid <= 0) {
        echo "Bid amount must be greater than zero.";
        header("refresh:5; url=listing.php?item_id=$item_id");
        exit();
    }

    // Check if the auction is still active
    if (!isAuctionActive($item_id)) {
        echo "This auction has already ended.";
        header("refresh:5; url=listing.php?item_id=$item_id");
        exit();
    }

    // Get the current highest bid amount for the item
    $sql = "SELECT GREATEST(
                (SELECT starting_price FROM Item WHERE item_ID = ?),
                (SELECT COALESCE(MAX(bid_price), 0) FROM Bid WHERE item_ID = ?)
            ) AS highest_price;";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $item_id, $item_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $highest_price = $row['highest_price'];

    // Check if the new bid is higher than the current highest bid
    if ($bid <= $highest_price) {
        echo "Your bid must exceed the current highest bid (£$highest_price).";
        header("refresh:5; url=listing.php?item_id=$item_id");
        exit();
    } else {
        // Check if the user has already placed a bid for this item
        if (checkExist($user_id, $item_id)) {
            // Update the existing bid
            $sql = "UPDATE Bid SET bid_price = ?, bid_time = NOW() WHERE item_ID = ? AND buyer_ID = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("dii", $bid, $item_id, $user_id);
            $stmt->execute();
        } else {
            // Insert a new bid
            $sql = "INSERT INTO Bid (bid_time, buyer_ID, item_ID, bid_price) VALUES (NOW(), ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iid", $user_id, $item_id, $bid);
            $stmt->execute();
        }

        // Add the user to the watchlist if not already watching
        if (!isUserWatching($user_id, $item_id)) {
            $sql = "INSERT INTO Watch (buyer_ID, item_ID) VALUES (?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $user_id, $item_id);
            $stmt->execute();
        }

        // Notify users watching this item
        // including those who have placed bids on this item or added it to their watchlist
        watchingEmail($item_id, $bid, $user_email);

        echo "Bid successfully placed! Redirecting to the listing page...";
        echo "<br><a href='listing.php?item_id=$item_id'>Click here if not redirected automatically.</a>";
        header("refresh:5; url=listing.php?item_id=$item_id");
        exit();
    }
} else {
    echo "Invalid operation.";
    header("refresh:5; url=../index.php");
    exit();
}
$conn->close();

// Function to check if the auction is still active
function isAuctionActive($item_id) {
    global $conn;
    $sql = "SELECT end_date FROM Item WHERE item_ID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $item_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if (!$row) {
        return false; // Item does not exist
    }

    $end_time = new DateTime($row['end_date']);
    $now = new DateTime();
    return $now <= $end_time;
}

// Function to check if the user has already placed a bid for the item
function checkExist($user_id, $item_id) {
    global $conn;
    $sql = "SELECT bid_ID FROM Bid WHERE item_ID = ? AND buyer_ID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $item_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    return ($result->num_rows > 0);
}

// Function to check if the user is already watching the item
function isUserWatching($user_id, $item_id) {
    global $conn;
    $sql = "SELECT * FROM Watch WHERE buyer_ID = ? AND item_ID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $user_id, $item_id);
    $stmt->execute();
    $result = $stmt->get_result();

    return ($result->num_rows > 0);
}

// Notify watchers about the new bid
function watchingEmail($item_id, $new_price, $user_email) {
    global $conn;
    $sql = "SELECT Buyer.email, Buyer.user_ID
            FROM Buyer, Watch
            WHERE Watch.item_ID = ? AND Watch.buyer_ID = Buyer.user_ID";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $item_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $email = $row['email'];
        //echo $email;
        $receiver_name = "User " . $row['user_ID'];
        if ($email === $user_email) {
            $subject = "Bid placed successfully";
            $message = "You have created a bid of £$new_price successfully on item $item_id.\n
                        If you don't want to receive the notification on this item, go to listing page of this item and remove yourself from the watchlist. ";
            send_email($email, $receiver_name, $subject, $message);
        }
        else{
            $subject = "New bid on watched item";
            $message = "The item $item_id has a new bid of £$new_price. Place a higher bid to stay competitive!\n
                        If you no longer want to receive the notification on this item, go to listing page of this item and remove yourself from the watchlist. ";

            send_email($email, $receiver_name, $subject, $message);
        }
    }
}
?>
<?php include_once("footer.php"); ?>

