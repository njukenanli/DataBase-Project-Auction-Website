<?php
require("../utilities.php");
if ($_SERVER["REQUEST_METHOD"] == "POST" and isset($_POST["buyer_id"]) and isset($_POST["item_id"]) and isset($_POST["enquiry"])) {
    if (empty($_POST["enquiry"])) {
        echo "enquiry cannot be empty! redirecting...";
        header("refresh:5;url=../../index.php");
    }
    else {
        $conn = ConnectDB("../data/config.json");
        $sql = "INSERT INTO Enquiry (enquiry_time, item_ID, buyer_ID, enquiry)  
                VALUES (NOW(), ". $_POST["item_id"] . ", " . $_POST["buyer_id"] . ", '" . $_POST["enquiry"] . "')";
        if ($conn->query($sql) === false) {
            die("Error inserting enquiry: " . $conn->error);
        }
        else {
            echo "Enquiry set successfully! Redirecting...";
        }
        $sql = "SELECT Seller.email FROM Seller, Item WHERE Seller.user_ID = Item.seller_ID AND Item.item_ID = " . $_POST["item_id"];
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
            $seller_email = $result->fetch_assoc()["email"];
        }
        else {
            die("Seller not found!");
        }
        $content = "A buyer has asked you a question: " . $_POST["enquiry"] . "\n Go to your enquiry page to answer it!";
        send_email(
            $seller_email, 
            "Dear Seller", 
            "A buyer has asked you a question", 
            $content,
            "../email/config.json"
        );
        header("refresh:5;url=../../index.php");
    }
}
else {
    die("failed to get POST information!");
}
?>