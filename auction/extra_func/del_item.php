<?php
// Include PHPMailer files, ensure correct paths
require __DIR__ .'/../email/autoload.php'; // using the composer autoloader

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Include utilities.php, ensure correct paths
require __DIR__ . '/../utilities.php';  // Including utilities.php from the upper-level directory

// Get POST data
if (!isset($_POST['item_id'])) {
    die("Invalid request. Missing required parameters.");
}

$item_id = intval($_POST['item_id']);

try {
    // Set configuration file path to data/config.json
    $config_path = __DIR__ . '/../data/config.json';  // Ensure the config.json file in the data directory is referenced

    // Check if the configuration file exists
    if (!file_exists($config_path)) {
        throw new Exception("Configuration file not found at $config_path");
    }

    // Read the configuration file
    $config_content = file_get_contents($config_path);
    if ($config_content === false) {
        throw new Exception("Failed to read configuration file at $config_path");
    }

    // Parse JSON configuration
    $config_data = json_decode($config_content, true);
    if ($config_data === null) {
        throw new Exception("Invalid JSON in configuration file.");
    }

    // Check database connection configuration
    if (!isset($config_data['servername']) || !isset($config_data['username']) || !isset($config_data['password']) || !isset($config_data['dbname'])) {
        throw new Exception("Database configuration is incomplete in config file.");
    }

    // Create database connection
    $conn = new mysqli($config_data['servername'], $config_data['username'], $config_data['password'], $config_data['dbname']);
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    $conn->begin_transaction();

    // Delete all bids related to the item
    $sql = "DELETE FROM Bid WHERE item_ID = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("SQL preparation failed: " . $conn->error);
    }
    $stmt->bind_param("i", $item_id);
    if (!$stmt->execute()) {
        throw new Exception("Failed to delete bids: " . $stmt->error);
    }
    $stmt->close();

    // Delete all watch list records related to the item
    $sql = "DELETE FROM Watch WHERE item_ID = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("SQL preparation failed: " . $conn->error);
    }
    $stmt->bind_param("i", $item_id);
    if (!$stmt->execute()) {
        throw new Exception("Failed to delete watch list entries: " . $stmt->error);
    }
    $stmt->close();

    // Get all buyer emails who are watching the item
    $sql = "SELECT Buyer.email 
            FROM Watch 
            JOIN Buyer ON Watch.buyer_ID = Buyer.user_ID 
            WHERE Watch.item_ID = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("SQL preparation failed: " . $conn->error);
    }
    $stmt->bind_param("i", $item_id);
    $stmt->execute();
    $result = $stmt->get_result();

    // Notify all buyers watching the item
    $subject = "Auction item #$item_id has been cancelled";
    $message = "We regret to inform you that auction item #$item_id has been cancelled.";
    while ($row = $result->fetch_assoc()) {
        $email = $row['email'];
        // Use the send_email function in utilities.php to send notifications
        send_email($email, "Valued Buyer", $subject, $message, __DIR__ . '/../email/config.json');
    }
    $stmt->close();

    // Delete the item
    $sql = "DELETE FROM Item WHERE item_ID = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("SQL preparation failed: " . $conn->error);
    }
    $stmt->bind_param("i", $item_id);
    if (!$stmt->execute()) {
        throw new Exception("Failed to delete item: " . $stmt->error);
    }
    $stmt->close();

    // Commit transaction
    $conn->commit();

    echo "Item and related records deleted successfully. Redirecting...";
    header("refresh:5;url=../../index.php");
} catch (Exception $e) {
    // Rollback transaction
    if (isset($conn)) {
        $conn->rollback();
    }
    die("Error: " . $e->getMessage());
} finally {
    // Close connection
    if (isset($conn)) {
        $conn->close();
    }
}
?>
