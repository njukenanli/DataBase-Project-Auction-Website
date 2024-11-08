 <?php
require_once("utilities.php");

if (!isset($_POST['functionname']) || !isset($_POST['arguments'])) {
  return;
}

// Extract arguments from the POST variables:
$arg = $_POST['arguments'];
$item_ID = $arg[0];

session_start();
$loggedin = (isset($_SESSION['logged_in']) and $_SESSION['logged_in']);
if ($loggedin){
  $email = $_SESSION['username'];
  if ($_SESSION['account_type'] === 'buyer'){
    $conn = connectDB();
    $sql = "SELECT user_ID FROM Buyer WHERE email = '" . $email . "'";
    $result = $conn->query($sql);
    if ($result->num_rows === 1){
      $id = $result->fetch_assoc()["user_ID"];
    }
    else{
      die("Wrong number of results:" . $result->num_rows);
    }
    if ($_POST['functionname'] == "add_to_watchlist") {
      // DONE: Update database and return success/failure.
        $sql = "INSERT INTO Watch (buyer_ID, item_ID) VALUES (". $id ."," . $item_ID . ")";
        if ($conn->query($sql) === TRUE) {
          $res = "success";
        }
        else{
          $res = "Data Insertion Failed: " . $conn->error . "<br>";
        }

    }
    else if ($_POST['functionname'] == "remove_from_watchlist") {
      // DONE: Update database and return success/failure.
      $sql = "DELETE FROM Watch WHERE item_ID = " . $item_ID . " AND buyer_ID = ". $id;
      if ($conn->query($sql) === TRUE) {
        $res = "success";
      }
      else{
        $res = "Data Deletion Failed: " . $conn->error . "<br>";
      }
    }
    else {
      $res = "error";
    }
    $conn->close();
  }
  else {
    $res = "seller";
  }
}
else {
  $res = "unlogged";
}

// Note: Echoing from this PHP function will return the value as a string.
// If multiple echo's in this file exist, they will concatenate together,
// so be careful. You can also return JSON objects (in string form) using
// echo json_encode($res).
echo $res;

?>