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
	session_start();
	if (!isset($_SESSION['logged_in']) || $_SESSION['account_type'] != 'buyer'){
	header("Location: browse.php");
	exit();
	}
	
	//get user's email
	$user_email = $_SESSION['username'];
	
	//connect with the database
	$conn = ConnectDB();

  // DONE: Perform a query to pull up auctions they might be interested in.
	$sql = "SELECT Item.item_ID, Category.name AS title, Item.description, MAX(Bid.bid_price) AS bid_price, COUNT(Bid.bid_ID) AS num_bids, Item.end_date
		FROM Bid, Item, Category, Buyer
		WHERE Bid.item_ID IN (SELECT item_ID FROM Bid, Buyer WHERE Buyer.user_ID = Bid.buyer_ID AND Buyer.email = ?) AND
      			Buyer.email != ? AND
			Buyer.user_ID = Bid.buyer_ID AND
			Bid.item_ID = Item.item_ID AND
			Item.category_ID = Category.category_ID
		GROUP BY Item.item_ID
		ORDER BY bid_time DESC;";
	if($stmt = $conn->prepare($sql)){
		$stmt->bind_param("ss", $user_email, $user_email);
		$stmt->execute();

		//get results
		$result = $stmt->get_result();
		if($result->num_rows > 0){
			echo "<ul class = 'list-group'>";

			//DONE: print out all the results as list items
			while($row = $result -> fetch_assoc()){
				$item_id = $row['item_ID'];
				$title = $row['title'];
                    		$desc = $row['description'];
                    		$price = $row['bid_price'];
                    		$num_bids = $row['num_bids'];
                    		$end_time = $row['end_date'];
				print_listing_li($item_id, $title, $desc, $price, $num_bids, $end_time);
			}
			echo "</ul>";
		} else {
			echo "<p> More exciting to come... </p>";
		}
		$stmt -> close();
	} else {
		echo "<p>Error querying the database.</p>";
	}
	$conn->close();
?>
</div>
<?php include_once("footer.php")?>
