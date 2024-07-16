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
    echo($errorMsg);
    exit;
}

// Preparing a safe SQL statement
$query = "SELECT " . mysqli_real_escape_string($conn, $columns) . " FROM " . mysqli_real_escape_string($conn, $table_name) . " ORDER BY " . mysqli_real_escape_string($conn, $table_name) . "_id DESC";
if ($stmt = $conn->prepare($query)) {
    // Execute the statement
    $stmt->execute();

    // Get the result set
    $result = $stmt->get_result();
    echo "<tr>" .
    "<th>Customer ID</th>" .
    "<th>First Name</th>" .
    "<th>Last Name</th>" .
    "<th>Email</th>" .
    "<th>Address</th>" .
    "<th>Number</th>" .
    "<th>Points</th>" .
    "<th>Join Date</th>" .
    "<th>Actions</th>" .
    "</tr>";
    // Display data in Cards Item
    while ($row = $result->fetch_assoc()) {
        echo "<tr class ='itemcontent active'>" .
        "<td class='customer_id'>CID" . htmlspecialchars($row['customer_id']) . "</td>" .
        "<td class='customer_fname'>" . htmlspecialchars($row['customer_fname']) . "</td>" .
        "<td class='customer_lname'>" . htmlspecialchars($row['customer_lname']) . "</td>" .
        "<td class='customer_email'>" . htmlspecialchars($row['customer_email']) . "</td>" .
        "<td class='customer_address'>" . htmlspecialchars($row['customer_address']) . "</td>" .
        "<td class='customer_number'>" . htmlspecialchars($row['customer_number']) . "</td>" .
        "<td>" . htmlspecialchars($row['customer_points']) . "</td>" .
        "<td>" . htmlspecialchars($row['customer_joindate']) . "</td>" .
        "<td>" .
        "<div class='action-container'>" .
        "<a href='edit.php?customerid=" . htmlspecialchars($row['customer_id']) . "'><button class='btn btn-warning edit-button'>Edit</button></a>" .
        "<form class='action-form' action='process/delete_process.php?customerid=" . htmlspecialchars($row['customer_id']) . "' method='post'>" .
        "<input type='hidden' name='customer_id' value='" . htmlspecialchars($row['customer_id']) . "'>" .
        "<input class='password-text' type='password' name='admin_pwd' placeholder='Admin Password' required>" .
        "<input class='btn btn-danger' type='submit' name='submit' value='Delete'>" .
        "</form>" .
        "<div>" .
        "</td>" .
        "</tr>";
    }
    // Close the statement
    $stmt->close();
} else {
    echo "Failed to prepare the statement";
}

function limit_text($text, $limit) {
    if (str_word_count($text, 0) > $limit) {
        $words = str_word_count($text, 2);
        $pos = array_keys($words);
        $text = substr($text, 0, $pos[$limit]) . '...';
    }
    return $text;
}
mysqli_close($conn);
?>
