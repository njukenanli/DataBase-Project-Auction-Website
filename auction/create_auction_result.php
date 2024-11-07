//This function takes the form data and adds the new auction to the database.

/* TODO #1: Connect to MySQL database (perhaps by requiring a file that
            already does this). */

/* TODO #2: Extract form data into variables. Because the form was a 'post'
            form, its data can be accessed via $POST['auctionTitle'], 
            $POST['auctionDetails'], etc. Perform checking on the data to
            make sure it can be inserted into the database. If there is an
            issue, give some semi-helpful feedback to user. */

/* TODO #3: If everything looks good, make the appropriate call to insert
            data into the database. */
            
// If all is successful, let user know.
<?php
include_once("header.php");
require("utilities.php");

// 开启会话，以确保能够访问会话变量
session_start();

echo '<div class="container my-5">';

// 连接到数据库
$conn = ConnectDB();

// 提取并验证表单数据
$title = $_POST['auctionTitle'] ?? '';
$details = $_POST['auctionDetails'] ?? '';
$category_name = $_POST['auctionCategory'] ?? '';
$starting_price = $_POST['auctionStartPrice'] ?? 0;
$reserve_price = $_POST['auctionReservePrice'] ?? 0;
$end_date = $_POST['auctionEndDate'] ?? '';

// 检查必填字段
if (empty($title) || empty($category_name) || empty($starting_price) || empty($end_date)) {
    die("Please fill in all required fields.");
}

// 获取 category_ID
$category_query = "SELECT category_ID FROM Category WHERE name = ?";
$stmt = $conn->prepare($category_query);
$stmt->bind_param("s", $category_name);
$stmt->execute();
$result = $stmt->get_result();
$category_id = $result->fetch_assoc()['category_ID'] ?? null;

if (!$category_id) {
    die("Invalid category selected.");
}

// 检查用户是否具有销售权限
if (!isset($_SESSION['account_type']) || $_SESSION['account_type'] !== 'seller') {
    die("Only sellers can create auctions.");
}

$seller_id = $_SESSION['user_id']; // 假设用户 ID 存储在会话中

// 插入新拍卖记录
$insert_query = "INSERT INTO Item (description, seller_ID, category_ID, starting_price, reserve_price, end_date) 
                 VALUES (?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($insert_query);
$stmt->bind_param("siidds", $title, $seller_id, $category_id, $starting_price, $reserve_price, $end_date);

if ($stmt->execute()) {
    echo '<div class="text-center">Auction successfully created! <a href="mylistings.php">View your new listing.</a></div>';
} else {
    echo "Error creating auction: " . $stmt->error;
}

// 关闭数据库连接
$conn->close();
echo '</div>';
?>

<?php include_once("footer.php"); ?>
