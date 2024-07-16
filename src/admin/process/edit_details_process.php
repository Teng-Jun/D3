<?php

include "../sessions/sessiontimeout.php";
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['role'] !== 'admin') {
    header("Location: ../../index.php");
    exit;
}

// Include the configuration file
$config = require 'config.php';
//Product ID
$specificid = $_GET['specificid'];
//Category
$cate = $_GET['cate'];

// Create a new mysqli object with the configuration parameters
$conn = new mysqli(
        $config['servername'],
        $config['username'],
        $config['password'],
        $config['dbname']
);

if ($conn->connect_error) {
    $errorMsg = "Connection failed: " . $conn->connect_error;
    echo($errorMsg);
    exit;
   
}

$table_name = filter_input(INPUT_GET, 'table', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$columns = filter_input(INPUT_GET, 'columns', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$specificid = filter_input(INPUT_GET, 'specificid', FILTER_SANITIZE_NUMBER_INT);
$cate = filter_input(INPUT_GET, 'cate', FILTER_SANITIZE_FULL_SPECIAL_CHARS);


if ($cate == "order") {
    $stmt = mysqli_prepare($conn, "SELECT $columns FROM mechkeys.$table_name WHERE {$table_name}_id = ?");
} else {
    $stmt = mysqli_prepare($conn, "SELECT $columns FROM $table_name WHERE {$table_name}_id = ?");
}


if ($stmt) {
    mysqli_stmt_bind_param($stmt, 'i', $specificid); // Ensure the ID is treated as an integer
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    while ($row = mysqli_fetch_assoc($result)) {
        if ($cate == "customer") {
            echo '<h4>Editing Customer Details</h4>';
            echo "<form class='action-form' action='process/save_process.php?customerid=" . htmlspecialchars($row[$table_name . '_id']) . "' method='post'>" .
                "<input type='hidden' name='customer_id' value='" . htmlspecialchars($row[$table_name . '_id']) . "' required>" .
                "<div class='form-group'>" .
                "<label for='first_name'>First Name</label>" .
                "<input class='form-control' type='text' name='customer_fname' value='" . htmlspecialchars($row[$table_name . '_fname']) . "'>" .
                "</div>" .
                "<div class='form-group'>" .
                "<label for='last_name'>Last Name</label>" .
                "<input class='form-control' type='text' name='customer_lname' value='" . htmlspecialchars($row[$table_name . '_lname']) . "' required>" .
                "</div>" .
                "<div class='form-group'>" .
                "<label for='email'>Email</label>" .
                "<input class='form-control' type='email' name='customer_email' value='" . htmlspecialchars($row[$table_name . '_email']) . "' required>" .
                "</div>" .
                "<div class='form-group'>" .
                "<label for='address'>Address</label>" .
                "<input class='form-control' type='text' name='customer_address' value='" . htmlspecialchars($row[$table_name . '_address']) . "' required>" .
                "</div>" .
                "<div class='form-group'>" .
                "<label for='phone_number'>Phone Number</label>" .
                "<input class='form-control' type='number' name='customer_number' value='" . htmlspecialchars($row[$table_name . '_number']) . "' required>" .
                "</div>" .
                "<div class='form-group'>" .
                "<label for='admin_pwd'>Enter Admin Password</label>" .
                "<input class='form-control password-text' type='password' name='admin_pwd' placeholder='Admin Password' required>" .
                "</div>" .
                "<input class='btn btn-success mt-3' type='submit' name='submit' value='Save'>" .
                "</form>";
        }
        if ($cate == "product") {
            echo '<h4>Editing Product Details</h4>';    
            echo "<form class='action-form' action='process/save_process.php?productid=" . htmlspecialchars($row[$table_name . '_id']) . "' method='post'>" .
                "<input type='hidden' name='product_id' value='" . htmlspecialchars($row[$table_name . '_id']) . "' required>" .
                "<div class='form-group'>" .
                "<label for='product_name'>Product Name</label>" .
                "<input class='form-control' type='text' name='product_name' value='" . htmlspecialchars($row[$table_name . '_name']) . "' required>" .
                "</div>" .
                "<div class='form-group'>" .
                "<label for='product_cost'>Product Cost(SGD)</label>" .
                "<input class='form-control' type='text' name='product_cost' value='" . htmlspecialchars($row[$table_name . '_cost']) . "' required>" .
                "</div>" .
                "<div class='form-group'>" .
                "<label for='product_cost'>Product Category<br>Barebone Kits: 5<br>Cables: 4<br>Keyboard: 3<br>keycaps: 2<br>Switches: 1</label>" .
                "<input class='form-control' type='number' name='category_id' value='" . htmlspecialchars($row['category_id']) . "' required>" .
                "</div>" .
                "<div class='form-group'>" .
                "<label for='product_sd'>Product Short Description</label>" .
                "<input class='form-control' type='text' name='product_sd' value='" . htmlspecialchars($row[$table_name . '_sd']) . "' required>" .
                "</div>" .
                "<div class='form-group'>" .
                "<label for='prodcut_ld'>Product Long Description</label>" .
                "<textarea class='form-control' name='product_ld' rows='5' required>" . htmlspecialchars($row[$table_name . '_ld']) . "</textarea>" .
                "</div>" .
                "<div class='form-group'>" .
                "<label for='product_quantity'>Product Quantity</label>" .
                "<input class='form-control' type='number' name='product_quantity' value='" . htmlspecialchars($row[$table_name . '_quantity']) . "' required>" .
                "</div>" .
                "<div class='form-group'>" .
                "<label for='admin_pwd'>Enter Admin Password</label>" .
                "<input class='form-control password-text' type='password' name='admin_pwd' placeholder='Admin Password' required>" .
                "</div>" .
                "<input class='btn btn-success mt-3' type='submit' name='submit' value='Save'>" .
                "</form>";
        
        }
        if ($cate == "order") {
            echo '<h4>Editing Order Details</h4>';
            echo "<form class='action-form' action='process/save_process.php?orderid=" . htmlspecialchars($row[$table_name . '_id']) . "' method='post'>" .
                "<input type='hidden' name='order_id' value='" . htmlspecialchars($row[$table_name . '_id']) . "' required>" .
                "<div class='form-group'>" .
                "<label for='order_tracking_no'>Order Tracking No</label>" .
                "<input class='form-control' type='text' name='order_tracking_no' value='" . htmlspecialchars($row[$table_name . '_tracking_no']) . "' required>" .
                "</div>" .
                "<div class='form-group'>" .
                "<label for='order_status'>Order Status</label>";
                if (htmlspecialchars($row[$table_name . '_status'])) {
                    echo "<p class='text-danger' style='background-color: #FFFF00'>" . "Current Status: " . htmlspecialchars($row[$table_name . '_status']) . "</p>";
                } else {
                    echo "<p class='text-danger' style='background-color: #FFFF00'>" . "Current Status: None" . "</p>";
                }
                echo "<select class='form-control' name='order_status'>" .
                "<option value = 'Awaiting Fufillment'>Awaiting Fufillment</option>" .
                "<option value = 'Shipped'>Shipped</option>" .
                "<option value = 'Delivered'>Delivered</option > " .
                "</select>" .
                "</div>" .
                "<div class='form-group'>" .
                "<label for='admin_pwd'>Enter Admin Password</label>" .
                "<input class='form-control password-text' type='password' name='admin_pwd' placeholder='Admin Password' required>" .
                "</div>" .
                "<input class='btn btn-success mt-3' type='submit' name='submit' value='Save'>" .
                "</form>";
            
        }
    }
    // Close the statement
    mysqli_stmt_close($stmt);
} 
// Close the database connection
mysqli_close($conn);
?>