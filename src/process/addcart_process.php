<?php
session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: ../login.php");
    exit;
}
addItemsCart();

function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function addItemsCart() {
    if (!empty($_POST['num_item']) && !empty($_POST['productid'])) {
        $quantity = sanitize_input($_POST['num_item']);
        $product = sanitize_input($_POST['productid']);
        $stock = sanitize_input($_POST['stock']);

        // Check if 'cart' session exists, if not create it
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        // Calculate total requested quantity including what's already in the cart
        $totalRequested = $quantity;
        if (isset($_SESSION['cart'][$product])) {
            $totalRequested += $_SESSION['cart'][$product]['qty'];
        }

        if ($totalRequested > $stock) {
            $_SESSION['errorMsg'] = "Quantity exceeds available stock.";
            header("Location: ../cart.php");
            exit;
        }

        // Check if product already exists in the cart
        if (isset($_SESSION['cart'][$product])) {
            // Add new quantity to existing quantity
            $_SESSION['cart'][$product]['qty'] += intval($quantity);
        } else {
            // Add new product to cart
            $_SESSION['cart'][$product] = array('qty' => intval($quantity));
        }
        
        // Redirect to cart.php after adding the item to the cart
        header("Location: ../cart.php");
    }
}
?>