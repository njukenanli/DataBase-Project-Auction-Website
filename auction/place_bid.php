// DONE: Extract $_POST variables, check they're OK, and attempt to make a bid.
// Notify user of success/failure and redirect/give navigation options.
// Can limit valid bids only to those that can outbid the current highest one, otherwise return failure. 
// DONE: Check whether the current bid outbids somebody and send emails to those outbidded.
// DONE: Send emails to those who are in the watchlist and are watching this item about this new bid.
// DONE: After making a bid, add the buyer to watchlist directly.
//Sending email function: email($receiver, $email, $title, $message){}

<?php include_once("header.php");
<?php require("utilities.php");

session_start();

//checking user's login state
if(!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']){
	header("Location: browse.php");
	exit();
}

//getting related info from the URL
if(isset($_GET['item_id']) && isset($_POST['bid'])){
	$item_id = $_GET['item_id'];
	$bid = floatval($_POST['bid']);

	//checking whether the bid amount is larger than 0
	if($bid <= 0){
		die("Bid amount must be larger than zero!");
	}

	//connecting to database
	$conn = ConnectDB();

	//getting the highest bid amount of the item
	$sql = "SELECT MAX(bid_price) as highest_price
		FROM Bid
		WHERE Bid.item_ID = ?";
	$stmt = $conn->prepare($sql);
	$stmt->bind_param("i", $item_id);
	$stmt->execute();
	$result = $stmt->get_result();
	$row = $result->fetch_assoc();
	$highest_price = $row['highest_price'];

	//whether the new bid amount is higher than the highest price
	//if not higher, notify failure
	if($bid <= $highest_price){
		die("Your bid should be higher than the current highest bids: £" . $highest_price);
	
	//if higher, insert the new bid amount into the bid table when there is not existing bidding record, edit 	the highest bid price when there is existing bidding record
	} else {
		//checking whether there is an existing bid record for the same user on the same item
		$user_id = $_SESSION['user_id'];
		if(checkExist($usera_id, $item_id)){
			$sql = "UPDATE Bid SET bid_price = ?, bid_time = NOW()
				WHERE item_ID = ? AND buyer_ID = ?";
			$stmt = $conn->prepare($sql);
			$stmt->bind_param("dii", $bid, $item_id, $user_id);
			$stmt->execute();
			echo "Bid updated successfully!";
		} else {
			$user_id = $_SESSION['user_id'];
			$sql = "INSERT INTO Bid (bid_time, buyer_ID, item_ID, bid_price)
				VALUES (NOW(), ?, ?, ?)";
			$stmt = $conn->prepare($sql);
			$stmt->bind_param("iid", $user_id, $item_id, $bid);
		
			if($stmt->execute()){
				echo "Bid successfully!";
			
				//add the buyer to watchlist directly
				$sql = "INSERT INTO Watch (buyer_ID, item_ID)
					VALUES (?, ?)";
				$stmt = $conn->prepare($sql);
				$stmt->bind_param("ii", $user_id, $item_id);
				$stmt->execute();
			
				//send emails to those outbidded
				outbiddedEmail($item_id, $bid);
			} else {
				die("Error bidding" . $conn->error);
		}	
		
		//send emails to those in the watchlist and are watching this item about this new bid
		watchingEmail($item_id, $bid);

		$stmt->close();
		$conn->close();

		//after bidding, redirect to the listing page
		header("Location: listing.php");
		exit();
	} 
} else {
	die("Invalid operation.");
}

?>
<?php include_once("footer.php"); ?>

function checkExist($user_id, $item_id){
	$sql = "SELECT bid_ID
		FROM Bid
		WHERE item_ID = ? AND
			buy_ID = ?";
	$stmt = $conn->prepare($sql);
	$stmt->bind_param("ii", $item_id, $user_id);
	$stmt->execute();
	$result = $stmt->get_result();

	return ($result->num_rows > 0)
}

function outbidedEmail($item_id, $new_price){
	$conn = ConnectDB();
	$sql = "SELECT Buyer.email, Buyer.user_ID
		FROM Buyer, Bid
		WHERE Bid.bid_price < ? AND
			Bid.item_ID = ? AND
			Bid.buyer_ID = Buyer.user_ID";
	$stmt = $conn->prepare($sql);
	$stmt->bind_param("ii", $bid, $item_id);
	$stmt->execute();
	$result = $stmt->get_result();

	while($row = $result->fetch_assoc()){
		$email = $row['email'];
		$receiver = $row['user_ID'];
		email($receiver, $email, "Your bid was exceeded", "Sorry, your bid was exceeded. The current bid price is £$new_price");
	}
	
	$conn->close();
}

function watchingEmail($item_id, $new_price){
$conn = ConnectDB();
	$sql = "SELECT Buyer.email, Buyer.user_ID
		FROM Buyer, Watch
		WHERE Watch.item_ID = ? AND
			Watch.buyer_ID = Buyer.user_ID;
	$stmt = $conn->prepare($sql);
	$stmt->bind_param("i", $item_id);
	$stmt->execute();
	$result = $stmt->get_result();

	while($row = $result->fetch_assoc()){
		$email = $row['email'];
		$receiver = $row['user_ID'];
		email($receiver, $email, "Your watching bid has new price", "The $item_id has new price. The current bid price is £$new_price. If you wish to continue to participate in the auction, please bid in time!");
	}
	
	$conn->close();
}
