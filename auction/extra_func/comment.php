<?php
require_once("../utilities.php");

$seller_id = $_GET['seller_id'];
$item_id = $_GET['item_id'];
$conn = ConnectDB("../data/config.json");
$sql = "SELECT email FROM Seller WHERE user_ID = " . $seller_id;
$result = $conn->query($sql);
if ($result->num_rows > 0){
    $email = $result->fetch_assoc()["email"];
}
else {
    die("Seller not found.");
}
echo "Seller No." . $seller_id . ", email: " . $email . "<br>";

$sql = "CREATE TEMPORARY TABLE SellerComment AS 
        (SELECT Comment.item_ID, Comment.comment, Comment.rating 
        From Comment, Item 
        WHERE Comment.item_ID = Item.item_ID 
        AND Item.seller_ID = ". $seller_id . ")";
if ($conn->query($sql) === false) {
    die("Error creating database: " . $conn->error);
}
$sql = "SELECT SellerComment.comment, SellerComment.rating, Buyer.user_ID, Buyer.email 
        FROM SellerComment, Buyer, Bid, (SELECT Bid.item_ID, MAX(bid_price) AS price 
            From Bid, SellerComment 
            WHERE SellerComment.item_ID = Bid.item_ID 
            GROUP BY Bid.item_ID) AS Price 
        WHERE Bid.item_ID = Price.item_ID 
        AND Bid.bid_price = Price.price 
        AND Bid.buyer_ID = Buyer.user_ID 
        AND SellerComment.item_ID = Bid.item_ID";
$result = $conn->query($sql);
$num = $result->num_rows;
if ($num > 0) {
    $sum = 0.0;
    $list = array();
    while($row = $result->fetch_assoc()) {
        array_push($list, array($row["user_ID"], $row["email"], $row["rating"], $row["comment"]));
        $sum += $row["rating"];
    }
    echo "Seller average rating: " . number_format(($sum/$num),2) . "/5.00<br><br>";
    echo "Comments:<br><br>";
    $i = 0;
    while($i < $num){
        echo "Buyer No." . $list[$i][0] . ", email:" . $list[$i][1] . "<br>";
        echo "rating: " . $list[$i][2] . "<br>";
        echo $list[$i][3] . "<br><br>";
        $i++;
    }
}
else {
    echo "No comment about this seller yet...<br>";
}


$conn->close();

echo '<div class="p-2 mr-5"><h5><a href="../listing.php?item_id=' . $item_id . '">' . "back to item page" . '</a></h5></div>';

?>