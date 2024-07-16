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
// Include the configuration file
$config = require 'config.php';

// Create a new mysqli object with the configuration parameters
$conn = new mysqli(
        $config['servername'],
        $config['username'],
        $config['password'],
        $config['dbname']
);

// Check for connection errors
if ($conn->connect_error) {
    $errorMsg = "Connection failed: " . $conn->connect_error;
    echo($errorMsg);
    exit;
}

// Prepare the SQL statement safely
$query = "SELECT " . mysqli_real_escape_string($conn, $columns) . " FROM " . mysqli_real_escape_string($conn, $table_name) .
         " INNER JOIN category ON " . mysqli_real_escape_string($conn, $table_name) . ".category_id = category.category_id " .
         " ORDER BY " . mysqli_real_escape_string($conn, $table_name) . "_id DESC";

// Prepare the statement
if ($stmt = $conn->prepare($query)) {
    // Execute the statement
    $stmt->execute();

    // Get the result set
    $result = $stmt->get_result();
    echo "<tr>" .
    "<th>Product ID</th>" .
    "<th>Product Name</th>" .
    "<th>Product Cost(SGD)</th>" .
    "<th>Category</th>" .
    "<th>Short Description</th>" .
    "<th>Long Description</th>" .
    "<th>Quantity</th>" .
    "<th>Actions</th>" .
    "</tr>";

    // Display data in Cards Item
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr class ='itemcontent active'>" .
        "<td class='product_id'>PID" . htmlspecialchars($row['product_id']) . "</td>" .
        "<td class='product_name'>" . htmlspecialchars($row['product_name']) . "</td>" .
        "<td class='product_cost'>" . htmlspecialchars($row['product_cost']) . "</td>" .
        "<td class='category_name'>" . htmlspecialchars($row['category_name']) . "</td>" .
        "<td class='product_sd'>" . htmlspecialchars($row['product_sd']) . "</td>" .
        "<td class='product_ld'>" . htmlspecialchars($row['product_ld']) . "</td>" .
        "<td class='product_quantity'>" . htmlspecialchars($row['product_quantity']) . "</td>" .
        "<td>" .
        "<div class='action-container'>" .
        "<a href='edit.php?productid=" . htmlspecialchars($row['product_id']) . "'><button class='btn btn-warning edit-button'>Edit</button></a>" .
        "<form class='action-form' action='process/delete_process.php?productid=" . htmlspecialchars($row['product_id']) . "' method='post'>" .
        "<input type='hidden' name='product_id' value='" . htmlspecialchars($row['product_id']) . "'>" .
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
?>