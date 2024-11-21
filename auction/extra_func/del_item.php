<? php
#TODO: get item_id, delete the item and all related bids, watch_list rows from the database.
#TODO: inform all the buyers in the watchlist of this item about this cancellation.
echo "auction deleted successfully, redirecting...";
header("refresh:5;url=../../index.php");
?>