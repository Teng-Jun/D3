<?php
include "../sessions/sessiontimeout.php";
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['role'] !== 'admin') {
    header("Location: ../../index.php");
    exit;
}

// $success = true;
// Include the configuration file
$config = require_once 'config.php';

require_once '../../process/log.php';

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
// Get client IP
$ip = $_SERVER['REMOTE_ADDR'];
$admin_id = $_SESSION['admin_id'];
$admin_pwd = filter_input(INPUT_POST, 'admin_pwd', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

// Prepare statement to fetch hashed password
$stmt = $conn->prepare("SELECT admin_password FROM admin WHERE admin_id = ?");
$stmt->bind_param("s", $admin_id);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $hashed_password = $row['admin_password'];
    if (!password_verify($admin_pwd, $hashed_password)) {
        echo "<script>alert('Invalid Admin Password'); history.go(-1);</script>";
        exit;
    }
} else {
    echo "<script>alert('Admin not found.'); history.go(-1);</script>";
    exit;
}

$stmt->close();

// Proceed with updating data after password verification
if (isset($_GET['productid'])) {
    $product_id = sanitize_input($_POST['product_id']);
    $product_name = sanitize_input($_POST['product_name']);
    $product_cost = sanitize_input($_POST['product_cost']);
    $category_id = sanitize_input($_POST['category_id']);
    $product_sd = sanitize_input($_POST['product_sd']);
    $product_ld = sanitize_input($_POST['product_ld']);
    $product_quantity = sanitize_input($_POST['product_quantity']);

    $stmt = $conn->prepare("UPDATE product SET product_name = ?, product_cost = ?, category_id = ?, product_sd = ?, product_ld = ?, product_quantity = ? WHERE product_id = ?");
    $stmt->bind_param("ssisssi", $product_name, $product_cost, $category_id, $product_sd, $product_ld, $product_quantity, $product_id);
    $stmt->execute();

    handleResponse($stmt, 'productlist.php', 'product', $product_id);
} elseif (isset($_GET['customerid'])) {
    $customer_id = sanitize_input($_POST['customer_id']);
    $customer_fname = sanitize_input($_POST['customer_fname']);
    $customer_lname = sanitize_input($_POST['customer_lname']);
    $customer_email = sanitize_input($_POST['customer_email']);
    $customer_address = sanitize_input($_POST['customer_address']);
    $customer_number = sanitize_input($_POST['customer_number']);

    $stmt = $conn->prepare("UPDATE customer SET customer_fname = ?, customer_lname = ?, customer_email = ?, customer_address = ?, customer_number = ? WHERE customer_id = ?");
    $stmt->bind_param("ssssis", $customer_fname, $customer_lname, $customer_email, $customer_address, $customer_number, $customer_id);
    $stmt->execute();

    handleResponse($stmt, 'customerlist.php', 'customer', $customer_id);
} elseif (isset($_GET['orderid'])) {
    $order_id = sanitize_input($_POST['order_id']);
    $order_tracking_no = sanitize_input($_POST['order_tracking_no']);
    $order_status = sanitize_input($_POST['order_status']);

    $stmt = $conn->prepare("UPDATE `order` SET order_tracking_no = ?, order_status = ? WHERE order_id = ?");
    $stmt->bind_param("ssi", $order_tracking_no, $order_status, $order_id);
    $stmt->execute();

    handleResponse($stmt, 'orderlist.php', 'order', $order_id);
}

// Function to handle response
function handleResponse($stmt, $redirect, $category, $id) {
    global $admin_id, $ip;
    if ($stmt->affected_rows > 0) {     
        logMessage("application.log", "$category $id details have been updated by admin $admin_id from IP $ip");
        echo "<script>alert('Update successful. " . $stmt->affected_rows . " rows affected.'); window.location.href = '../" . $redirect . "';</script>";
    } else {
        echo "<script>alert('Update failed. No rows affected.'); window.location.href = '../" . $redirect . "';</script>";
    }
    $stmt->close();
}



function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

$conn->close();
?>