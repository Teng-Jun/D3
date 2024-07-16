<?php

include "../sessions/sessiontimeout.php";
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['role'] !== 'admin') {
    header("Location: ../../index.php");
    exit;
}

//table name
$table_name = $_GET['table'];
//columns name
$columns = $_GET['columns'];
//page name
// Create database connection.
$config = require 'config.php';
// Create a new mysqli object with the configuration parameters
$conn = new mysqli(
        $config['servername'],
        $config['username'],
        $config['password'],
        $config['dbname']
);

// Check connection
if ($conn->connect_error) {
    $errorMsg = "Connection failed: " . $conn->connect_error;
    ($errorMsg);
    exit();
}

// Sanitize input to ensure basic security
$table_name = mysqli_real_escape_string($conn, $_GET['table']);
$columns = mysqli_real_escape_string($conn, $_GET['columns']);


// Prepare the SQL statement using sanitized variables
$query = "SELECT $columns FROM `$table_name` " .
         "INNER JOIN `customer` ON `$table_name`.`customer_id` = `customer`.`customer_id` " .
         "JOIN `product` on `$table_name`.`product_id` = `product`.`product_id` " .
         "ORDER BY `$table_name`.`order_id` DESC";

if ($stmt = $conn->prepare($query)) {
    // Execute the statement
    $stmt->execute();

    // Get the result set
    $result = $stmt->get_result();

    echo "<tr>" .
    "<th>Order ID</th>" .
    "<th>Order Tracking No</th>" .
    "<th>Order Quantity</th>" .
    "<th>Order Status</th>" .
    "<th>Product ID</th>" .
    "<th>Product Name</th>" .
    "<th>Product Cost(SGD)</th>" .
    "<th>Customer ID</th>" .
    "<th>Customer Name</th>" .
    "<th>Customer Address</th>" .
    "<th>Actions</th>" .
    "</tr>";

    // Display data in Cards Item
    while ($row = $result->fetch_assoc()) {
        echo "<tr class ='itemcontent active'>" .
        "<td class='order_id'>OID" . htmlspecialchars($row['order_id']) . "</td>" .
        "<td class='order_tracking_no'>" . htmlspecialchars($row['order_tracking_no']) . "</td>" .
        "<td class='order_quantity'>" . htmlspecialchars($row['order_quantity']) . "</td>" .
        "<td class='order_status'>" . htmlspecialchars($row['order_status']) . "</td>" .
        "<td class='product_id'>PID" . htmlspecialchars($row['product_id']) . "</td>" .
        "<td class='product_name'>" . htmlspecialchars($row['product_name']) . "</td>" .
        "<td class='product_cost'>" . htmlspecialchars($row['product_cost']) . "</td>" .
        "<td class='customer_id'>CID" . htmlspecialchars($row['customer_id']) . "</td>" .
        "<td class='customer_name'>" . htmlspecialchars($row['customer_name']) . "</td>" .
        "<td class='customer_address'>" . htmlspecialchars($row['customer_address']) . "</td>" .
        "<td>" .
        "<div class='action-container'>" .
        "<a href='edit.php?orderid=" . htmlspecialchars($row['order_id']) . "'><button class='btn btn-warning edit-button'>Edit</button></a>" .
        "<form class='action-form' action='process/delete_process.php?orderid=" . htmlspecialchars($row['order_id']) . "' method='post'>" .
        "<input type='hidden' name='order_id' value='" . htmlspecialchars($row['order_id']) . "'>" .
        "<input class='password-text' type='password' name='admin_pwd' placeholder='Admin Password' required>" .
        "<input class='btn btn-danger' type='submit' name='submit' value='Delete'>" .
        "</form>" .
        "</div>" .
        "</td>" .
        "</tr>";
    }
        // Close the statement
    $stmt->close();
} else {
    echo "Failed to prepare the statement";
}

// Close the database connection
mysqli_close($conn);

function limit_text($text, $limit) {
    if (str_word_count($text, 0) > $limit) {
        $words = str_word_count($text, 2);
        $pos = array_keys($words);
        $text = substr($text, 0, $pos[$limit]) . '...';
    }
    return $text;
}

?>
