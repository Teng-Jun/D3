<?php
session_start();
if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
    header("Location: ../keycaps.php");
    exit;
}

if (!isset($_POST['csrf_token'], $_SESSION['csrf_token'])|| $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    header("Location: ../keycaps.php");
    exit;
}

//table name
$table_name = $_POST['table'];
//columns name
$columns = $_POST['columns'];

// Include the configuration file
$config = require 'config.php';

// Create a new mysqli object with the configuration parameters
$conn = new mysqli(
        $config['servername'],
        $config['username'],
        $config['password'],
        $config['dbname']
);

// Graceful handling of connection error
if ($conn->connect_error) {
    $_SESSION['errorMsg'] = "Connection failed: " . $conn->connect_error;
    header("Location: ../keycaps.php");
    exit();
}

// Prepare the statement
$stmt = mysqli_prepare($conn, "SELECT $columns FROM $table_name");

// Execute the statement
mysqli_stmt_execute($stmt);

// Get the result set
$result = mysqli_stmt_get_result($stmt);

// Display data in Cards Item
while ($row = mysqli_fetch_assoc($result)) {
    $image_name = strtolower(str_replace(' ', '', $row[$table_name . '_name']));
    if (limit_text($row['product_quantity'], 10) > 0 && $row['category_id']==3) {
        echo 
        "<div class = 'card_container content col-lg-3 col-md-6 col-sm-6 col-12 active'>" .
        "<a href='productdetails.php?id=" . htmlspecialchars($row['product_id']) . "'>" .
        "<div class='card h-100'>" .
        "<img class='card-img-top' src='images/keycaps/" . htmlspecialchars($image_name) . ".jpg' alt='Card image cap' loading='lazy'>" .
        "<div class='card-body'>" .
        "<h5 class='card-title'>" . htmlspecialchars($row['product_name']) . "</h5>" .
        "<p class='card-text'>" . htmlspecialchars(limit_text($row['product_sd'], 10)) . "</p>" .
        "<p class='card-price'><strong>SGD$" . htmlspecialchars(limit_text($row['product_cost'], 10)) . "</strong></p>" .
        "<p class='card-text'>Stock: " . htmlspecialchars(limit_text($row['product_quantity'], 10)) . "</p>" .
        "</div>" .
        "</div>" .
        "</a>" .
        "</div>";
    }
    else if (limit_text($row['product_quantity'], 10) == 0 && $row['category_id']==3) 
    {
        echo 
        "<div class = 'card_container content col-lg-3 col-md-6 col-sm-6 col-12 active'>" .
        "<a href='productdetails.php?id=" . htmlspecialchars($row['product_id']) . "'>" .
        "<div class='card h-100'>" .
        "<img class='card-img-top' src='images/keycaps/" . htmlspecialchars($image_name) . ".jpg' alt='Card image cap' loading='lazy'>" .
        "<div class='card-body'>" .
        "<h5 class='card-title'>" . htmlspecialchars($row['product_name']) . "</h5>" .
        "<p class='card-text'>" . htmlspecialchars(limit_text($row['product_sd'], 10)) . "</p>" .
        "<p class='card-price'><strong>SGD$" . htmlspecialchars(limit_text($row['product_cost'], 10)) . "</strong></p>" .
        "<h5 class='text-danger'>Out Of Stock</h5>" .
        "</div>" .
        "</div>" .
        "</a>" .
        "</div>";
        
    }
}

// Close prepared statement and database connection
    mysqli_stmt_close($stmt);
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


