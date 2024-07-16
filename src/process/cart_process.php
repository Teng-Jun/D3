<?php
session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

// Initialize cart if not set
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Ensure these GET parameters are checked before use
$table_name = isset($_GET['table']) ? $_GET['table'] : 'default_table';
$columns = isset($_GET['columns']) ? $_GET['columns'] : 'default_columns';
$productid = isset($_GET['productid']) ? $_GET['productid'] : null;

// Include the configuration file
$config = require 'config.php';

// Create a new mysqli object with the configuration parameters
$conn = new mysqli(
        $config['servername'],
        $config['username'],
        $config['password'],
        $config['dbname']
);

foreach ($_SESSION['cart'] as $productId => $value) {
    // Prepare the statement
    $stmt = mysqli_prepare($conn, "SELECT * FROM $table_name INNER JOIN category ON $table_name.category_id = category.category_id WHERE $table_name.product_id = ?");

    // Check if the statement was prepared successfully
    if ($stmt === false) {
        die("Error preparing statement: " . mysqli_error($conn));
    }

    // Bind the product ID to the statement
    mysqli_stmt_bind_param($stmt, "i", $productId);

    // Execute the statement
    $result = mysqli_stmt_execute($stmt);

    // Check if the execution was successful
    if ($result === false) {
        die("Error executing statement: " . mysqli_stmt_error($stmt));
    }

    // Get the result set
    $result = mysqli_stmt_get_result($stmt);

    // Fetch the row
    $row = mysqli_fetch_assoc($result);

    if ($row) {
        $image_name = strtolower($row['category_name'] . '/' . str_replace(' ', '', $row[$table_name . '_name']));

        echo "<div class='card'>";
        echo "<div class='row product-information'>";
        echo "<div class='col-md-9'>";
        echo "<div class='row'>";
        echo "<div class='col-md-6'>";
        echo "<img class='product-image' src='images/" . htmlspecialchars($image_name) . ".jpg' alt='Card image cap' loading='lazy'>";
        echo "</div>";
        echo "<div class='col-md-6'>";
        echo "<h5 class='card-title mt-2'>" . htmlspecialchars($row[$table_name . '_name']) . "</h5>";
        echo "<p class='card-text'>" . htmlspecialchars($row[$table_name . '_sd']) . "</p>";
        echo "<strong>Item Description:</strong><br>";
        echo "<p class='card-description'>" . htmlspecialchars(limit_text($row[$table_name . '_ld'], 200)) . "</p>";
        echo "<p class='card-price'><strong>SGD$" . htmlspecialchars($row[$table_name . '_cost']) . "</strong></p>";
        echo "<p>Stock: " . htmlspecialchars($row[$table_name . '_quantity']) . "</p>";
        echo "</div>";
        echo "</div>";
        echo "</div>";
        echo "<div class='col-md-3 justify-content-center align-self-center'>";
        echo "<p class='card-text'>";
        echo "<input type='hidden' id='" . htmlspecialchars($row[$table_name . '_id']) . "' name='" . htmlspecialchars($row[$table_name . '_id']) . "'>";
        echo "<div class='counter justify-content-center align-self-center'>";
        echo "<span class='down' onClick='decreaseCount(event, this)'>-</span>";
        echo "<input name='num_item' id='num_item' type='number' value='" . htmlspecialchars($_SESSION['cart'][$row[$table_name . '_id']]['qty']) . "'  maxlength='2' max='" .htmlspecialchars($row[$table_name . '_quantity']) . "' min='1'>";
        echo "<span class='up' onClick='increaseCount(event, this)'>+</span>";
        echo "</div>";
        echo "<button class='addtocart mt-2' type='button'  onclick='updateQuantity(" .htmlspecialchars($row[$table_name . '_id']) .")'>Update Quantity</button>";
        echo "<button class='addtocart delete mt-2' type='button' onclick='deleteItem(" . htmlspecialchars($row[$table_name . '_id']) . ")'>Delete Item</button>";
        echo "</div>";
        echo "</div>";
        echo "</div>";
    }

    // Close the statement
    mysqli_stmt_close($stmt);
}

function limit_text($text, $limit) {
    if (strlen($text) > $limit) {
        $text = substr($text, 0, $limit) . '...';
    }
    return $text;
}
?>

