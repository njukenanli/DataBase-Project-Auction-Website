<?php
// This file should be run as a scheduled event. Run it independently at the back stage!
require_once("../utilities.php");
$run_interval = 10; //seconds
function logging ($msg) {
  file_put_contents("log.txt", $msg, FILE_APPEND | LOCK_EX);
}
function success($conn, $item_id, $seller_id, $price, $desc) {
    $get_buyer = "SELECT Buyer.user_ID, Buyer.email FROM Buyer, Bid   
                WHERE Bid.item_ID = $item_id   
                    AND Bid.bid_price = $price  
                    AND Bid.buyer_ID = Buyer.user_ID";
    $result = $conn->query($get_buyer);
    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        $buyer_email = $row["email"];
        $buyer_id = $row['user_ID'];
        $get_seller = "SELECT email FROM Seller WHERE user_ID = $seller_id";
        $result = $conn->query($get_seller);
        $seller_email = $result->fetch_assoc()["email"];
        logging("success email sent ...\n");
        send_email($buyer_email, "Successful Buyer", "You have won the auction!",
                "Dear buyer,\n
                you have won the item No.$item_id, $desc. \n
                Please contact seller $seller_email to buy this item. \n
                You can click this item in the auction website, 
                go to the item description page, 
                and make comments about this deal. \n",
                "../email/config.json");
        send_email($seller_email, "Successful Seller", "Your item has been sold out successfully!",
                "Dear seller,\n
                your item No.$item_id, $desc has been sold out to buyer No.$buyer_id, email $buyer_email.\n
                The buyer will contact you to buy the item.\n",
                "../email/config.json");
    }
    else{
        logging("The number of results != 1");
    }
}
function failure($conn, $item_id, $seller_id, $desc) {
    $get_seller = "SELECT email FROM Seller WHERE user_ID = $seller_id";
    $result = $conn->query($get_seller);
    $seller_email = $result->fetch_assoc()["email"];
    logging("failure email sent ...\n");
    send_email($seller_email, "Seller", "Your auction falied to make a deal",
    "Dear seller, unfortunately, your auction $item_id, $desc ended without making a deal.\n",
    "../email/config.json");
}

$conn = ConnectDB("../data/config.json");
$sql = "(SELECT Bid.item_ID, MAX(Bid.bid_price) AS end_price, Item.reserve_price, Item.seller_ID, Item.description  
        FROM Item, Bid  
        WHERE Item.end_date <= NOW()    
            AND Item.processed = FALSE  
            AND Item.item_ID = Bid.Item_ID   
        GROUP BY Bid.item_ID)   
        UNION   
        (SELECT Item.item_ID, Item.starting_price AS end_price, Item.reserve_price, Item.seller_ID, Item.description   
        FROM Item    
        WHERE Item.end_date <= NOW()    
        AND Item.processed = FALSE  
        AND NOT EXISTS (SELECT * FROM Bid WHERE Bid.item_ID = Item.item_ID) ) ";


file_put_contents("log.txt", "", LOCK_EX); //clear log file.
set_time_limit(0);
while (true) {
    logging("process excuted...\n");
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
          if ($row["end_price"] >= $row["reserve_price"]){
            success($conn, $row["item_ID"], $row["seller_ID"], $row["end_price"], $row["description"]);
          }
          else {
            failure($conn, $row["item_ID"], $row["seller_ID"], $row["description"]);
          }
          flush();
          ob_flush();
          $set_processed = "UPDATE Item 
                            SET processed = TRUE, end_date = end_date  
                            WHERE item_ID =". $row["item_ID"];
          if ($conn->query($set_processed) === FALSE) {
            logging("Execution Failure: " . $conn->error);
          } 
          $comment = "INSERT INTO Comment (item_ID) VALUES (".$row["item_ID"].")";
          if ($conn->query($comment) === FALSE) {
            logging("Execution Failure: " . $conn->error);
          }
        }
    }
    sleep($run_interval); //seconds
}
$conn->close();

?>