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
    // exit();
    echo "<tr>" .
    "<th>Admin ID</th>" .
    "<th>Email</th>" .
    "<th>Created By (ID)</th>" .
    "<th>Approved By (ID)</th>" .
    "<th>Approved</th>" .
    "<th>Deleted By (ID)</th>" .
    "<th>Actions</th>" .
    "</tr>";
    // Display data in Cards Item
    while ($row = $result->fetch_assoc()) {
        echo "<tr class ='itemcontent active'>" .
        "<td class='admin_id'>" . htmlspecialchars($row['admin_id']) . "</td>" .
        "<td class='admin_email'>" . htmlspecialchars($row['admin_email']) . "</td>" .
        "<td class='created_by'>" . htmlspecialchars($row['created_by']) . "</td>" .
        "<td class='customer_email'>" . htmlspecialchars($row['approved_by']) . "</td>" .
        "<td class='customer_address'>" . htmlspecialchars($row['approved']) . "</td>" .
        "<td class='customer_number'>" . htmlspecialchars($row['deleted_by']) . "</td>" .
        "<td>" .
        "<div class='action-container'>" .
        "<a href='process/approve_process_admin.php?admin_id=" . htmlspecialchars($row['admin_id']) . "'><button class='btn btn-warning edit-button'>Approve</button></a>" .
        "<a href='process/delete_process_admin.php?admin_id=" . htmlspecialchars($row['admin_id']) . "'><button class='btn btn-danger edit-button'>Delete</button></a>" .
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
