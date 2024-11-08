<?php include_once("header.php")?>
<?php require("utilities.php")?>

<?php
  // Get info from the URL:
  $item_id = $_GET['item_id'];

  // DONE: Use item_id to make a query to the database.
  $conn = ConnectDB();
  $sql =  "CREATE TEMPORARY TABLE HighestBidPrice AS
          ((SELECT item_ID, MAX(bid_price) AS price, COUNT(buyer_ID) AS num FROM Bid 
          WHERE item_ID = " . $item_id . " GROUP BY item_ID)
          UNION
          (SELECT Item.item_ID, Item.starting_price AS price, 0 AS num FROM Item WHERE 
          item_ID = " . $item_id
          . " AND (NOT EXISTS (SELECT Bid.item_ID FROM Bid WHERE Bid.item_ID = Item.item_ID))))";
  if ($conn->query($sql) === FALSE) {
    die("Excution Failure: " . $conn->error);
  } 
  $sql = "SELECT Category.name AS title, Item.description AS description, 
          HighestBidPrice.price AS current_price, HighestBidPrice.num AS num_bids, 
          Item.end_date AS end_date FROM Item, HighestBidPrice, Category 
          WHERE Item.item_ID = HighestBidPrice.item_ID AND Category.category_ID = Item.category_ID";
  $result = $conn->query($sql);
  if ($result->num_rows === 1) {
    $row = $result->fetch_assoc();
    $title = $row["title"];
    $description = $row["description"];
    $current_price = floatval($row["current_price"]);
    $num_bid = intval($row["num_bids"]);
    $end_time = new DateTime($row["end_date"]);
  }
  else {
    die("Wrong number of results:" . $result->num_rows);
  }
  
  // Calculate time to auction end:
  $now = new DateTime();
  
  if ($now < $end_time) {
    $time_to_end = date_diff($now, $end_time);
    $time_remaining = ' (in ' . display_time_remaining($time_to_end) . ')';
  }
  // DONE: If the user has a session, use it to make a query to the database
  //       to determine if the user is already watching this item.
  $has_session = (isset($_SESSION['logged_in']) and $_SESSION['logged_in']);
  if ($has_session) {
    $email = $_SESSION['username'];
    $sql =  "SELECT * FROM Watch, Buyer WHERE Watch.item_ID = " . $item_id 
    . " AND Watch.buyer_ID = Buyer.user_ID AND Buyer.email = '" . $email . "'";
    $result = $conn->query($sql);
    if ($result->num_rows > 0){
      $watching = true;
    }
    else{
      $watching = false;
    }
  }
  else {
    $email =  '';
    $watching = false;
  }
?>


<div class="container">

<div class="row"> <!-- Row #1 with auction title + watch button -->
  <div class="col-sm-8"> <!-- Left col -->
    <h2 class="my-3"><?php echo($title); ?></h2>
  </div>
  <div class="col-sm-4 align-self-center"> <!-- Right col -->
<?php
  /* The following watchlist functionality uses JavaScript, but could
     just as easily use PHP as in other places in the code */
  if ($now < $end_time):
?>
    <div id="watch_nowatch" <?php if ($has_session && $watching) echo('style="display: none"');?> >
      <button type="button" class="btn btn-outline-secondary btn-sm" onclick="addToWatchlist()">+ Add to watchlist</button>
    </div>
    <div id="watch_watching" <?php if (!$has_session || !$watching) echo('style="display: none"');?> >
      <button type="button" class="btn btn-success btn-sm" disabled>Watching</button>
      <button type="button" class="btn btn-danger btn-sm" onclick="removeFromWatchlist()">Remove watch</button>
    </div>
<?php endif /* Print nothing otherwise */ ?>
  </div>
</div>

<div class="row"> <!-- Row #2 with auction description + bidding info -->
  <div class="col-sm-8"> <!-- Left col with item info -->

    <div class="itemDescription">
    <?php echo($description); ?>
    </div>

  </div>
<div class="col-sm-8"> <!-- Left col with item info -->

  <div class="itemDescription">

<?php
if ($now >= $end_time) {
  // DONE: Note: Auctions that have ended may pull a different set of data,
  //       like whether the auction ended in a sale or was cancelled due
  //       to lack of high-enough bids.
  $sql = "SELECT reserve_price FROM Item WHERE item_ID = " . $item_id;
  $result = $conn->query($sql);
  if ($result->num_rows === 1){
    $reserve_prive = $result->fetch_assoc()["reserve_price"];
    if ($num_bid > 0 and $current_price >= $reserve_prive){
      echo "<br>A deal has been made on this item.<br>";
      echo "Final deal price: £" . $current_price . "<br>";
      $sql = "SELECT Buyer.user_ID, Buyer.email FROM Bid, Buyer 
      WHERE Buyer.user_ID = Bid.buyer_ID AND 
      Bid.bid_price = " . $current_price . " AND Bid.item_ID = " . $item_id;
      $result = $conn->query($sql);
      if ($result->num_rows === 1){
        $buyer = $result->fetch_assoc();
        $id = $buyer["user_ID"];
        $email = $buyer["email"];
        echo "Auction winner: No. " . $id . ", email: ". $email . "<br>";
      }
      else{
        die("Wrong number of results:" . $result->num_rows);
      }
    }
    else {
      echo "<br>Auction ended. The item failed to make a deal.<br>";
    }
  }
  else{
    die("Wrong number of results:" . $result->num_rows);
  }

}
?>

  </div>

</div>

  <div class="col-sm-4"> <!-- Right col with bidding info -->

    <p>
<!-- DONE: Print the result of the auction here -->
<?php 
if ($now >= $end_time) :
    echo "This auction ended at ";
    echo(date_format($end_time, 'j M H:i')) ;
  ?>
<?php else: ?>
     Auction ends <?php echo(date_format($end_time, 'j M H:i') . $time_remaining) ?></p>  
    <p class="lead">Current bid: £<?php echo(number_format($current_price, 2)) ?></p>
    <p class="lead">Bid number: <?php echo($num_bid) ?></p>

    <!-- Bidding form -->
    <form method="POST" action="place_bid.php">
      <div class="input-group">
        <div class="input-group-prepend">
          <span class="input-group-text">£</span>
        </div>
	    <input type="number" class="form-control" id="bid">
      </div>
      <button type="submit" class="btn btn-primary form-control">Place bid</button>
    </form>
<?php endif ?>

  
  </div> <!-- End of right col with bidding info -->

</div> <!-- End of row #2 -->



<?php 
$conn->close();
include_once("footer.php");
?>


<script> 
// JavaScript functions: addToWatchlist and removeFromWatchlist.

function addToWatchlist(button) {
  console.log("These print statements are helpful for debugging btw");

  // This performs an asynchronous call to a PHP function using POST method.
  // Sends item ID as an argument to that function.
  $.ajax('watchlist_funcs.php', {
    type: "POST",
    data: {functionname: 'add_to_watchlist', arguments: [<?php echo($item_id);?>]},

    success: 
      function (obj, textstatus) {
        // Callback function for when call is successful and returns obj
        console.log("Success");
        var objT = obj.trim();
 
        if (objT == "success") {
          $("#watch_nowatch").hide();
          $("#watch_watching").show();
        }
        else if (objT == "unlogged") {
          alert("Add to watch failed. Please log in first.");
        }
        else if (objT == "seller") {
          alert("Add to watch failed. This service is only for buyer. Please re-log in as a buyer first.");
        }
        else {
          alert("Add to watch failed. Please try again later.");
        }
      },

    error:
      function (obj, textstatus) {
        console.log("Error");
      }
  }); // End of AJAX call

} // End of addToWatchlist func

function removeFromWatchlist(button) {
  // This performs an asynchronous call to a PHP function using POST method.
  // Sends item ID as an argument to that function.
  $.ajax('watchlist_funcs.php', {
    type: "POST",
    data: {functionname: 'remove_from_watchlist', arguments: [<?php echo($item_id);?>]},

    success: 
      function (obj, textstatus) {
        // Callback function for when call is successful and returns obj
        console.log("Success");
        var objT = obj.trim();
 
        if (objT == "success") {
          $("#watch_watching").hide();
          $("#watch_nowatch").show();
        }
        else if (objT == "unlogged") {
          alert("Watch removal failed. Please log in first.");
        }
        else if (objT == "seller") {
          alert("Watch removal failed. This service is only for buyer. Please re-log in as a buyer first.");
        }
        else {
          alert("Watch removal failed. Try again later.");
        }
      },

    error:
      function (obj, textstatus) {
        console.log("Error");
      }
  }); // End of AJAX call

} // End of addToWatchlist func
</script>