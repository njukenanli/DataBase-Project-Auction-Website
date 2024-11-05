<?php
$file = file_get_contents('config.json');
$config = json_decode($file, true);
$clear_old_db = TRUE;

// Warning: when creating database and table, we use the root privilages. 
// But when running the program, it is suggested to create other accounts...
 
// Waring: Always check all the databases and tables mentioned below
// are not created or deleted before run this program !!!!!!

$conn = new mysqli($config["servername"], $config["username"], $config["password"]);
if ($conn->connect_error) {
    die("Connection Failure: " . $conn->connect_error);
} 
echo "Connect to SQL service Successfully!<br>";

// Drop database if it exists
if ($clear_old_db){
    $sql = "DROP DATABASE IF EXISTS ".$config["dbname"];
    if ($conn->query($sql) === TRUE) {
        echo "Database ".$config["dbname"]." dropped successfully if it existed.<br>";
    } else {
        die("Error dropping database: " . $conn->error);
    }
}

$sql = "CREATE DATABASE ".$config["dbname"];
if ($conn->query($sql) === TRUE) {
    echo "Successfully Created Database ".$config["dbname"]."!<br>";
} else {
    die("Error creating database: " . $conn->error);
}
$conn->close();

$conn = new mysqli($config["servername"], $config["username"], $config["password"], $config["dbname"]);
if ($conn->connect_error) {
    die("Connection Failure: " . $conn->connect_error);
} 
echo "Connect to SQL table ".$config["dbname"]." successfully!<br>";

$_sql = file_get_contents('CreateDB.sql');
$_arr = explode(';', $_sql);
foreach ($_arr as $_value) {
    if ($conn->query($_value) === TRUE) {
        echo $_value."<br>";
        echo "Table created successfully!<br>";
        } 
    else {
        echo "Table Creation Failed: " . $conn->error . "<br>";
    }
}
echo "Program Exits with No Error.<br>";
$conn->close();
?>