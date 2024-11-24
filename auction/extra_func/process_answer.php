<?php
require("../utilities.php");
if ($_SERVER["REQUEST_METHOD"] == "POST" and isset($_POST["enquiry_id"]) and isset($_POST["answer"]) and isset($_POST["buyer_email"])) {
    $conn = ConnectDB("../data/config.json");
    $sql = "UPDATE Enquiry SET answer = '" . $_POST["answer"] . "'  WHERE enquiry_ID = " . $_POST["enquiry_id"];
    if ($conn->query($sql) === false) {
        die("Error updating enquiry: " . $conn->error);
    }
    else {
        echo "Answer set successfully! Redirecting...";
    }
    $sql = "SELECT enquiry, answer FROM Enquiry WHERE enquiry_ID =" . $_POST["enquiry_id"];
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
    }
    else {
        die("Enquiry item not found!");
    }
    $conn->close();
    $content = "Dear Enquirer, the Seller Has answered your question. \n Question: " 
                . $row["enquiry"] . "\nAnswer: " . $row["answer"];
    send_email(
        $_POST["buyer_email"], 
        "Dear Enquirer", 
        "The Seller Has Answered your Question!", 
        $content,
        "../email/config.json"
    );
    header("refresh:5;url=../../index.php");
}
else {
    die("failed to get POST data!");
}

?>