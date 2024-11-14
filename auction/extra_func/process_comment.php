<?php
require_once("../utilities.php");


if (isset($_POST['comment']) and isset($_POST['rating']) and isset($_POST['item_id'])){
    $comment = $_POST['comment'];
    $rating = $_POST['rating'];
    $item_ID = $_POST['item_id'];
    
    if (strlen($rating) > 200){
        echo "Comment length should be less than 200 chars!  Redirecting...";
    }
    else{
        $conn = ConnectDB("../data/config.json");
        $sql = "UPDATE Comment 
                SET comment = '" . $comment . "', rating = " . $rating
                . " WHERE item_ID = " . $item_ID;
        // NOTE: The comment would be created and set empty when a deal is made. 
        // So later the comment can only be altered rather than created again.
        if ($conn->query($sql) === false) {
            die("Error updating comment: " . $conn->error);
        }
        else {
            echo "Comment set successfully! Redirecting...";
        }
        $conn->close();
    }
}
else {
    echo "Comment or user information is missing. Redirecting...";
}
header("refresh:5;url=../../index.php");
?>