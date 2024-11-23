<?php
// Include PHPMailer files, ensuring the correct path
require __DIR__ .'/../email/autoload.php'; // Using the composer autoloader

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Include utilities.php, ensuring the correct path
require __DIR__ . '/../utilities.php';  // Including utilities.php from the parent directory of extra_func

// Retrieve POST data
if (!isset($_POST['item_id']) || !isset($_POST['buyer_id'])) {
    die("Invalid request. Missing required parameters.");
}

$item_id = intval($_POST['item_id']);
$buyer_id = intval($_POST['buyer_id']);

try {
    // Update the config file path to data/config.json
    $config_path = __DIR__ . '/../data/config.json';  // Ensure referencing the config.json file in the data directory

    // Check if the config file exists
    if (!file_exists($config_path)) {
        throw new Exception("Configuration file not found: $config_path");
    }

    // Read the config file
    $config_content = file_get_contents($config_path);
    if ($config_content === false) {
        throw new Exception("Failed to read configuration file: $config_path");
    }

    // Parse the JSON configuration
    $config_data = json_decode($config_content, true);
    if ($config_data === null) {
        throw new Exception("Invalid JSON format in the configuration file.");
    }

    // Check database connection configuration
    if (!isset($config_data['servername']) || !isset($config_data['username']) || !isset($config_data['password']) || !isset($config_data['dbname'])) {
        throw new Exception("Incomplete database configuration in the config file.");
    }

    // Create a database connection
    $conn = new mysqli($config_data['servername'], $config_data['username'], $config_data['password'], $config_data['dbname']);
    if ($conn->connect_error) {
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }
    $conn->begin_transaction();

    // Delete the specified bid
    $sql = "DELETE FROM Bid WHERE item_ID = ? AND buyer_ID = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("SQL statement preparation failed: " . $conn->error);
    }
    $stmt->bind_param("ii", $item_id, $buyer_id);
    if (!$stmt->execute()) {
        throw new Exception("Failed to delete bid: " . $stmt->error);
    }
    $stmt->close();

    // Check if there are any remaining bids
    $sql = "SELECT MAX(bid_price) AS highest_bid FROM Bid WHERE item_ID = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("SQL statement preparation failed: " . $conn->error);
    }
    $stmt->bind_param("i", $item_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $highest_bid = isset($row['highest_bid']) ? $row['highest_bid'] : null;
    $stmt->close();

    // If there are no remaining bids, set the highest price to the starting price
    if ($highest_bid === null) {
        $sql = "SELECT starting_price FROM Item WHERE item_ID = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("SQL statement preparation failed: " . $conn->error);
        }
        $stmt->bind_param("i", $item_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $highest_bid = isset($row['starting_price']) ? $row['starting_price'] : 0;
        $stmt->close();
    }

    // Retrieve the email addresses of all buyers watching the item
