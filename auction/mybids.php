<?php
session_start();
include_once("header.php");
require("utilities.php");

#checking user's credential, otherwise swap to welcome page
if (!isset($_SESSION['logged_in']) || $_SESSION['account_type'] != 'buyer'){
	header("Location: browse.php");
	exit();
}

#get the current user's email
$user_email = $_SESSION['username'];
?>

<div class="container">

<h2 class="my-3">My bids</h2>

<?php
  // This page is for showing a user the auctions they've bid on.
  // It will be pretty similar to browse.php, except there is no search bar.
  // This can be started after browse.php is working with a database.
  // Feel free to extract out useful functions from browse.php and put them in
  // the shared "utilities.php" where they can be shared by multiple files.

//connect with the database
$conn = ConnectDB();

//perform a query to pull up the auctions they've bid on
$sql = "SELECT Item.item_ID, Item.title, Item.description, Bid.bid_price, Item.end_date,
	(SELECT COUNT(*) FROM Bid JOIN Item ON Bid.item_ID = Item.item_ID) AS num_bids
	FROM Buyer, Bid, Item
	WHERE Buyer.email = ?			AND
		Buyer.user_ID = Bid.buyer_ID	AND
    		Bid.item_ID = Item.item_ID
	GROUP BY Item.item_ID
	ORDER BY Bid.bid_time DESC";

//pre-processing searching results, loop through results
If ($stmt = $conn->prepare($sql)){
	$stmt->bind_param("s", $user_email);
	$stmt->execute();
	$result = $stmt->get_result();

	//print out all the results if there is
	if($result->num_rows > 0){
		echo "<ul class = ‘list-group’>";
		while ($row = $result -> fetch_assoc()){
			$item_id = $row['item_ID'];
			$title = $row['title'];
                    	$desc = $row['description'];
                    	$price = $row['bid_price'];
                    	$num_bids = $row['num_bids'];
                    	$end_time = new DateTime($row['end_date']);

			//call print list function
			print_listing_li($item_id, $title, $desc, $price, $num_bids, $end_time);
		}
		echo "</ul>";
	} else {
		//if there is no result, print out some hints
		echo "<p> You have not bid on anything...</p>";
	}
	//disconnect with database
	$stmt -> close();
} else {
	//if there is error when querying the database
	echo "<p>Error querying the database.</p>";
}
$conn->close();
?>
</div>

<?php include_once("footer.php")?>
