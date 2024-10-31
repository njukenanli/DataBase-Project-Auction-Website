<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "Auction";
$clear_old_db = TRUE;

// Warning: when creating database and table, we use the root privilages. 
// But when running the program, it is suggested to create other accounts...
 
// Waring: Always check all the databases and tables mentioned below
// are not created or deleted before run this program !!!!!!

$conn = new mysqli($servername, $username, $password);
if ($conn->connect_error) {
    die("Connection Failure: " . $conn->connect_error);
} 
echo "Connect to SQL service Successfully!<br>";

// Drop database if it exists
$sql = "DROP DATABASE IF EXISTS ".$dbname;
if ($conn->query($sql) === TRUE) {
    echo "Database ".$dbname." dropped successfully if it existed.<br>";
} else {
    die("Error dropping database: " . $conn->error);
}

$sql = "CREATE DATABASE ".$dbname;
if ($conn->query($sql) === TRUE) {
    echo "Successfully Created Database ".$dbname."!<br>";
} else {
    die("Error creating database: " . $conn->error);
}
$conn->close();

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection Failure: " . $conn->connect_error);
} 
echo "Connect to SQL table ".$dbname." successfully!<br>";

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