<?php include_once("header.php")?>
<?php require("utilities.php")?>

<div class="container">

<h2 class="my-3">Recommendations for you</h2>

<?php
  // This page is for showing a buyer recommended items based on their bid 
  // history. It will be pretty similar to browse.php, except there is no 
  // search bar. This can be started after browse.php is working with a database.
  // Feel free to extract out useful functions from browse.php and put them in
  // the shared "utilities.php" where they can be shared by multiple files.
  
// DONE: Check user's credentials (cookie/session).
if (!isset($_SESSION['logged_in']) || $_SESSION['account_type'] != 'buyer'){
header("Location: browse.php");
exit();
}
	
//connect with the database
$conn = ConnectDB();

//get user's id
$user_email = $_SESSION['username'];
$sql = "SELECT user_ID FROM Buyer WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $user_email);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$user_id = $row['user_ID'];

// DONE: Perform a query to pull up auctions they might be interested in.
//Create a temporary table to get other buyers have bidded the same item with the current buyer
$sql = "CREATE TEMPORARY TABLE otherBuyers AS
	SELECT b2.buyer_ID
	FROM Bid b1, Bid b2
	WHERE b1.item_ID = b2.item_ID 	AND
    		b1.buyer_ID = ?		AND
    		b2.buyer_ID != ?
	GROUP BY b2.buyer_ID";

//pre-processing the query statements
if ($stmt = $conn->prepare($sql)) {
    	$stmt->bind_param("ii", $user_id, $user_id);
    	$stmt->execute();
	$stmt->close();
} else {
	die ("Error creating temporary table.");
}
//Get recommended items from other buyers who have similar interest
//Excepting bidden items
$sql = "SELECT Item.item_ID,
		Item.title,
		Item.description,
		Item.end_date,
                                Item.image_path,
		(SELECT COUNT(bid_ID) FROM Bid WHERE Bid.item_ID = Item.item_ID) AS num_bids,
		(SELECT GREATEST(
			(SELECT starting_price FROM Item WHERE Bid.item_ID = Item.item_ID),
			(SELECT COALESCE(MAX(bid_price), 0) FROM Bid WHERE Bid.item_ID = Item.item_ID)
		)) AS bid_price
	FROM Bid, otherBuyers, Item
	WHERE Bid.buyer_ID = otherBuyers.buyer_ID AND
		Bid.item_ID = Item.item_ID AND
  		Item.item_ID NOT IN (SELECT item_ID FROM Bid WHERE buyer_ID = ?)";

//pre-processing query statement
if($stmt = $conn->prepare($sql)){
	$stmt->bind_param("i", $user_id);
	$stmt->execute();
	$result = $stmt->get_result();
	//Print out all the recommended items, if there is
	if($result->num_rows > 0){
		echo "<ul class = 'list-group'>";
		
		while($row = $result -> fetch_assoc()){
			$item_id = $row['item_ID'];
			$title = $row['title'];
            		$desc = $row['description'];
                    	$price = $row['bid_price'];
                    	$num_bids = $row['num_bids'];
        		$end_time = new DateTime($row['end_date']);
                                $image_path = $row['image_path'];

			//Call print list function
			print_listing_li($item_id, $title, $desc, $price, $num_bids, $end_time,$image_path);
		}
		
		echo "</ul>";
	} else {
		//If there is no recommened items, giving some hints
		echo "<p> More exciting to come... </p>";
	}
	$stmt -> close();
} else {
	//Give some hints when there is query error.
	echo "<p>Error querying the database.</p>";
}
$conn->close();
?>
</div>

<?php include_once("footer.php")?>

