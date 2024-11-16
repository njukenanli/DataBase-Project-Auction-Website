<?php include_once("header.php");
require("utilities.php");

session_start();

// DONE: Extract $_POST variables, check they're OK, and attempt to make a bid.
// Notify user of success/failure and redirect/give navigation options.
// Can limit valid bids only to those that can outbid the current highest one, otherwise return failure. 
// DONE: Check whether the current bid outbids somebody and send emails to those outbidded.
// DONE: Send emails to those who are in the watchlist and are watching this item about this new bid.
// DONE: After making a bid, add the buyer to watchlist directly.
//Sending email function: email($receiver, $email, $title, $message){}

//checking user's login state
if(!isset($_SESSION['logged_in']) || $_SESSION['account_type'] != 'buyer'){
	header("Location: browse.php");
	exit();
}

//connecting to database
$conn = ConnectDB();
if($conn->connect_error){
	echo("Connection failed: " . $conn->connect_error);
	header("refresh:5; url=listing.php?item_id=$item_id");
	exit;
};

//getting related info from the URL
$user_email = $_SESSION['username'];
$sql = "SELECT user_ID FROM Buyer WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $user_email);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$user_id = $row['user_ID'];

if(isset($_POST['item_id']) && isset($_POST['bid'])){
	$item_id = $_POST['item_id'];
	$bid = floatval($_POST['bid']);

	//checking whether the bid amount is larger than 0
	if($bid <= 0){
		echo('<div class = "text-centre">Bid amount must be larger than zero!</div>');
		header("refresh:5; url=listing.php?item_id=$item_id");
		exit;
	}

	//getting the highest bid amount of the item
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

	//whether the new bid amount is higher than the current highest price
	//if not higher, notify failure
	if($bid <= $highest_price){
		echo("Your bid should be higher than the current highest bids: £" . $highest_price);
		header("refresh:5; url=listing.php?item_id=$item_id");
		exit;
	
	//if higher, insert the new bid amount into the bid table when there is not existing bidding record
	//edit the highest bid price when there is existing bidding record
	} else {
		//checking whether there is an existing bid record for the same user on the same item
		if(checkExist($user_id, $item_id)){
			$sql = "UPDATE Bid SET bid_price = ?, bid_time = NOW()
				WHERE item_ID = ? AND buyer_ID = ?";
			$stmt = $conn->prepare($sql);
			$stmt->bind_param("dii", $bid, $item_id, $user_id);
			$stmt->execute();
			echo "Bid updated successfully!";
			header("refresh:5; url=listing.php?item_id=$item_id");
			exit;
		} else {

			$sql = "INSERT INTO Bid (bid_time, buyer_ID, item_ID, bid_price)
				VALUES (NOW(), ?, ?, ?)";
			$stmt = $conn->prepare($sql);
			$stmt->bind_param("iid", $user_id, $item_id, $bid);
		
			if($stmt->execute()){

				//add the buyer to watchlist directly
				$sql = "INSERT INTO Watch (buyer_ID, item_ID)
					VALUES (?, ?)";
				$stmt = $conn->prepare($sql);
				$stmt->bind_param("ii", $user_id, $item_id);
				$stmt->execute();
			
				//send emails to those outbidded
				outbiddedEmail($item_id, $bid);

				//send emails to those in the watchlist and are watching this item about this new bid
				watchingEmail($item_id, $bid);

				echo "Bid successfully!";
				header("refresh:5; url=listing.php?item_id=$item_id");
				exit;
			} else {

				echo("Error bidding" . $conn->error);
				header("refresh:5; url=listing.php?item_id=$item_id");
				exit;
			}
		}	

		//send emails to those in the watchlist and are watching this item about this new bid
		watchingEmail($item_id, $bid);

		$stmt->close();

		//after bidding, redirect to the listing page
		echo "Bid successfully placed!";
		header("refresh:5; url=listing.php?item_id=$item_id");
		exit;
	} 
} else {
	echo "Invalid operation.";
	header("refresh:5; url=$base_url/index.php");
	exit;
}
$conn->close();


function checkExist($user_id, $item_id){
	global $conn;
	$sql = "SELECT bid_ID FROM Bid WHERE item_ID = ? AND buyer_ID = ?";
	$stmt = $conn->prepare($sql);
	$stmt->bind_param("ii", $item_id, $user_id);
	$stmt->execute();
	$result = $stmt->get_result();

	return ($result->num_rows > 0);
}

function outbiddedEmail($item_id, $new_price){
	$sql = "SELECT Buyer.email, Buyer.user_ID
		FROM Buyer, Bid
		WHERE Bid.bid_price < ? AND
			Bid.item_ID = ? AND
			Bid.buyer_ID = Buyer.user_ID";
	$stmt = $conn->prepare($sql);
	$stmt->bind_param("di", $new_price, $item_id);
	$stmt->execute();
	$result = $stmt->get_result();

	while($row = $result->fetch_assoc()){
		$email = $row['email'];
		$receiver = $row['user_ID'];
		email($receiver, $email, "Your bid was exceeded", "Sorry, your bid was exceeded. The current bid price is £$new_price");
	}
}

function watchingEmail($item_id, $new_price){
	$sql = "SELECT Buyer.email, Buyer.user_ID
		FROM Buyer, Watch
		WHERE Watch.item_ID = ? AND
			Watch.buyer_ID = Buyer.user_ID";
	$stmt = $conn->prepare($sql);
	$stmt->bind_param("i", $item_id);
	$stmt->execute();
	$result = $stmt->get_result();

	while($row = $result->fetch_assoc()){
		$email = $row['email'];
		$receiver = $row['user_ID'];
		email($receiver, $email, "Your watching bid has new price", "The $item_id has new price. The current bid price is £$new_price. If you wish to continue to participate in the auction, please bid in time!");
	}
}

?>
<?php include_once("footer.php"); ?>
