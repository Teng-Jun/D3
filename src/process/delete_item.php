<?php
session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

$product_id = filter_input(INPUT_POST, 'product_id', FILTER_SANITIZE_NUMBER_INT);
if (isset($_SESSION['cart'][$product_id])) {
    unset($_SESSION['cart'][$product_id]);  // Remove the item from the cart
    echo 'Item deleted successfully';
} else {
    echo 'Item not found in cart';
    header("Location: index.php");
    exit();
}
?>