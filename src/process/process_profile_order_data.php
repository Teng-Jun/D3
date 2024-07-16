<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit;
}


// Include the config file
$config = include('config.php');

// Create database connection
$conn = new mysqli($config['servername'], $config['username'], $config['password'], $config['dbname']);

// Check connection
if ($conn->connect_error) {
    $_SESSION['errorMsg'][] = "Connection failed: " . $conn->connect_error;
    header("Location: ../profile.php"); // Redirect to an error page or handle the error as needed
    exit();
}

// Fetch customer data using customer_id from session
$customer_id = $_SESSION['customer_id'];
$sql = "SELECT * FROM mechkeys.customer WHERE customer_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$result = $stmt->get_result();
$customer = $result->fetch_assoc();

// Fetch orders for the customer
$order_sql = "SELECT * FROM `order` WHERE customer_id = ?";
$order_stmt = $conn->prepare($order_sql);
$order_stmt->bind_param("i", $customer_id);
$order_stmt->execute();
$order_result = $order_stmt->get_result();
$orders = $order_result->fetch_all(MYSQLI_ASSOC);

// Close the statements and connection
$stmt->close();
$order_stmt->close();
$conn->close();
?>
