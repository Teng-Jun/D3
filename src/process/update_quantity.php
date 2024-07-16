<?php

session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Assuming you have sanitized and validated the input
    $product_id = $_POST['product_id'];
    $new_quantity = $_POST['new_quantity'];

    // Update the session cart
    $_SESSION['cart'][$product_id]['qty'] = $new_quantity;

    // Send a success response
    // echo 'success';
    // Redirect to cart.php after adding the item to the cart
    header("Location: ../cart.php");

    exit;
}
header("Location: index.php");

// If the request method is not POST or if the parameters are missing, send an error response
// echo 'error';
exit;
?>
