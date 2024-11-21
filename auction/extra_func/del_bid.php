<? php
#TODO: get item_id and buyer_id from post, delete the bid from the database.
#TODO: if the bid in the database is the highest bid of this item, send an email to all the buyers in the watchlist about the change of the highest price, and the new highest bid price of this item (could be no bid left, be careful!)
echo "bid deleted successfully, redirecting...";
header("refresh:5;url=../../index.php");
?>