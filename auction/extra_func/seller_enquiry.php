<?php
require("../utilities.php");
$enquiry_id = $_GET['enquiry_id'];
$conn = ConnectDB("../data/config.json");
$sql = "SELECT Enquiry.buyer_ID, Enquiry.enquiry, Enquiry.answer, Buyer.email    
        FROM Enquiry, Buyer    
        WHERE Enquiry.enquiry_ID = $enquiry_id AND Enquiry.buyer_ID = Buyer.user_ID";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    echo "<br>Enquiry from buyer No." . $row["buyer_ID"] . ", email ". $row["email"] . ": <br>";
    echo $row["enquiry"];
    echo "<br>";
    echo "<br>Answer from seller: <br>";
    echo $row["answer"] ."<br><br>";
    echo '
    <form method="post" action="process_answer.php">
    You can make/edit answer here...
    <br><br>
    <input type="hidden" name="enquiry_id" value="' . $enquiry_id . '">
    <input type="hidden" name="buyer_email" value="' . $row["email"] . '">
    <label>Answer (no more than 200 characters) :</label>
      <div class="input-group w-100">
        <textarea class="form-control" id="answer" name="answer" rows="5" maxlength="200"></textarea>
      </div>
    </div>
    <div class="col-md-1 px-0">
      <button type="submit" class="btn btn-primary">Submit Answer</button>
    </div>
    ';
}
else {
    die("Enquiry item not found!");
}
$conn->close();

?>