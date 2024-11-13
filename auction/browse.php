<?php
session_start();
include_once("header.php");
require("utilities.php");
?>

<div class="container">

<h2 class="my-3">Browse listings</h2>

<div id="searchSpecs">
<!-- When this form is submitted, this PHP page is what processes it.
     Search/sort specs are passed to this page through parameters in the URL
     (GET method of passing data to a page). -->
<form method="get" action="browse.php">
  <div class="row">
    <div class="col-md-5 pr-0">
      <div class="form-group">
        <label for="keyword" class="sr-only">Search keyword:</label>
        <div class="input-group">
          <div class="input-group-prepend">
            <span class="input-group-text bg-transparent pr-0 text-muted">
              <i class="fa fa-search"></i>
            </span>
          </div>
          <input type="text" class="form-control border-left-0" id="keyword" name="keyword" placeholder="Search for anything">
        </div>
      </div>
    </div>
    <div class="col-md-3 pr-0">
      <div class="form-group">
        <label for="cat" class="sr-only">Search within:</label>
        <select class="form-control" id="cat" name="cat">
          <option selected value="all">All categories</option>
          <option value="china">china</option>
          <option value="painting">painting</option>
          <option value="sculpture">sculpture</option>
        </select>
      </div>
    </div>
    <div class="col-md-3 pr-0">
      <div class="form-inline">
        <label class="mx-2" for="order_by">Sort by:</label>
        <select class="form-control" id="order_by" name="order_by">
          <option selected value="pricelow">Price (low to high)</option>
          <option value="pricehigh">Price (high to low)</option>
          <option value="date">Soonest expiry</option>
        </select>
      </div>
    </div>
    <div class="col-md-1 px-0">
      <button type="submit" class="btn btn-primary">Search</button>
    </div>
  </div>
</form>
</div> <!-- end search specs bar -->

<?php
// Retrieve these from the URL
// Initialize variables to store query conditions
$condition = array();
$conn = ConnectDB();

// Create temporary table to store highest bid information
$sql =  "CREATE TEMPORARY TABLE HighestBidPrice AS
        ((SELECT item_ID, MAX(bid_price) AS price, COUNT(buyer_ID) AS num FROM Bid GROUP BY item_ID)
        UNION
        (SELECT Item.item_ID, Item.starting_price AS price, 0 AS num FROM Item WHERE NOT EXISTS
        (SELECT Bid.item_ID FROM Bid WHERE Bid.item_ID = Item.item_ID)))";
if ($conn->query($sql) === FALSE) {
  die("Execution Failure: " . $conn->error);
} 
array_push($condition, "HighestBidPrice.item_ID = Item.Item_ID", "Item.category_ID = Category.category_ID");

// Process query conditions
if (isset($_GET['keyword'])) {
  $keyword = $_GET['keyword'];
  if (!($keyword === "")){
    $keyword = strtolower($keyword);
    array_push($condition, "LOWER(Item.description) LIKE '%" . $keyword . "%'");
  }
}
if (isset($_GET['cat'])) {
  $category = $_GET['cat'];
  if (!($category === "all")){
    array_push($condition, "Category.name = \"" . $category . "\"");
  }
}
if (isset($_GET['order_by'])) {
  $ordering = $_GET['order_by'];
  switch ($ordering){
    case "pricelow":
      $order = " ORDER BY HighestBidPrice.price ASC";
      break;
    case "pricehigh":
      $order = " ORDER BY HighestBidPrice.price DESC";
      break;
    case "date":
      $order = " ORDER BY Item.end_date";
      break;
  }
}
else {
  $order = "";
}

if (!isset($_GET['page'])) {
  $curr_page = 1;
}
else {
  $curr_page = $_GET['page'];
}

// Use the above values to construct a query statement
$sql = "SELECT Item.item_ID AS item_id, Item.title AS title, 
        Item.description AS description, HighestBidPrice.price AS current_price, 
        HighestBidPrice.num AS num_bids, Item.end_date AS end_date "
        . "FROM Item, HighestBidPrice, Category "
        . "WHERE " . join(" AND ", $condition) 
        . $order;

$result = $conn->query($sql);

// Calculate total results for pagination
$num_results = $result->num_rows;
$results_per_page = 5;
$max_page = ceil($num_results / $results_per_page);
$list = array();

if ($result->num_rows > 0) {
  while($row = $result->fetch_assoc()) {
    array_push($list, array($row["item_id"],
                            $row["title"], 
                            $row["description"], 
                            floatval($row["current_price"]), 
                            intval($row["num_bids"]), 
                            new DateTime($row["end_date"])));
  }
}
?>
<div class="container mt-5">

<!-- If the result set is empty, display a message. -->
<?php
if ($num_results === 0){
  echo "No results found...<br>";
}
?>

<ul class="list-group">

<!-- Use a while loop to print each retrieved auction listing -->
<?php
$start = ($curr_page - 1) * $results_per_page;
$end = min($start + $results_per_page, $num_results);
while($start < $end) {
    $item = $list[$start];
    // Use the function defined in utilities.php
    print_listing_li($item[0], $item[1], $item[2], $item[3], $item[4], $item[5]);
    $start++;
}
$conn->close();
?>

</ul>

<!-- Pagination for search results -->
<nav aria-label="Search results pages" class="mt-5">
  <ul class="pagination justify-content-center">

<?php

// Copy any currently set GET variables to the URL
$querystring = "";
foreach ($_GET as $key => $value) {
  if ($key != "page") {
    $querystring .= "$key=$value&amp;";
  }
}

$high_page_boost = max(3 - $curr_page, 0);
$low_page_boost = max(2 - ($max_page - $curr_page), 0);
$low_page = max(1, $curr_page - 2 - $low_page_boost);
$high_page = min($max_page, $curr_page + 2 + $high_page_boost);

if ($curr_page != 1) {
  echo('
  <li class="page-item">
    <a class="page-link" href="browse.php?' . $querystring . 'page=' . ($curr_page - 1) . '" aria-label="Previous">
      <span aria-hidden="true"><i class="fa fa-arrow-left"></i></span>
      <span class="sr-only">Previous</span>
    </a>
  </li>');
}

for ($i = $low_page; $i <= $high_page; $i++) {
  if ($i == $curr_page) {
    echo('
  <li class="page-item active">');
  }
  else {
    echo('
  <li class="page-item">');
  }
  echo('
    <a class="page-link" href="browse.php?' . $querystring . 'page=' . $i . '">' . $i . '</a>
  </li>');
}

if ($curr_page != $max_page) {
  echo('
  <li class="page-item">
    <a class="page-link" href="browse.php?' . $querystring . 'page=' . ($curr_page + 1) . '" aria-label="Next">
      <span aria-hidden="true"><i class="fa fa-arrow-right"></i></span>
      <span class="sr-only">Next</span>
    </a>
  </li>');
}
?>

  </ul>
</nav>

</div>

<?php include_once("footer.php")?>
