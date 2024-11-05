<?php
// This program inserts persudo data into our empty database.
$file = file_get_contents('config.json');
$config = json_decode($file, true);
$conn = new mysqli($config["servername"], $config["username"], $config["password"], $config["dbname"]);

$_sql = file_get_contents('TestData.sql');
$_arr = explode(';', $_sql);
foreach ($_arr as $_value) {
    if ($conn->query($_value) === TRUE) {
        echo $_value."<br>";
        echo "Data Inserted successfully <br>";
        } 
    else {
        echo "Data Insertion Failed: " . $conn->error . "<br>";
    }
}
echo "Program Exits with No Error.<br>";

$conn->close();
?>