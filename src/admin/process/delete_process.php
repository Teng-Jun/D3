<?php

include "../sessions/sessiontimeout.php";
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['role'] !== 'admin') {
    header("Location: ../../index.php");
    exit;
}

// Include the configuration file
$config = require_once 'config.php';

require_once '../../process/log.php';

// Ensure there is a logged-in admin
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

// Create a new mysqli object with the configuration parameters
$conn = new mysqli(
    $config['servername'],
    $config['username'],
    $config['password'],
    $config['dbname']
);
if ($conn->connect_error) {
    $errorMsg = "Connection failed: " . $conn->connect_error;
    echo "<script>alert('$errorMsg'); window.history.go(-1);</script>";
    exit;
}


$ip = $_SERVER['REMOTE_ADDR'];
$admin_id = $_SESSION['admin_id'];
$admin_pwd = filter_input(INPUT_POST, 'admin_pwd', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

// Retrieve the hashed password from the database for the logged-in admin
$stmt = $conn->prepare("SELECT admin_password FROM mechkeys.admin WHERE admin_id = ?");
$stmt->bind_param("i", $admin_id); // Assuming the admin ID is stored in session
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    if (!password_verify($admin_pwd, $row['admin_password'])) {
        echo "<script>alert('Incorrect admin password.'); window.history.go(-1);</script>";
        exit;  // Ensure script stops executing if password is incorrect
    }
} else {
    echo "<script>alert('Admin user not found.'); window.history.go(-1);</script>";
    exit;  // Stop execution if no admin is found
}

$stmt->close();


// Check for the entity to be deleted
if (isset($_GET['customerid'])) {
    $customer_id = $_POST['customer_id'];
    handleDeletion('customer', $customer_id, $conn, $admin_id, $ip);
} elseif (isset($_GET['productid'])) {
    $product_id = $_POST['product_id'];
    handleDeletion('product', $product_id, $conn, $admin_id, $ip);
} elseif (isset($_GET['orderid'])) {
    $order_id = $_POST['order_id'];
    handleDeletion('order', $order_id, $conn, $admin_id, $ip);
}


function handleDeletion($type, $id, $mysqli, $admin_id, $ip)
{
    try {
        $stmt = mysqli_prepare($mysqli, "DELETE FROM `$type` WHERE `${type}_id` = ?");
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        $affected_rows = mysqli_stmt_affected_rows($stmt);

        if ($affected_rows > 0) {
            logMessage("application.log", "${type} $id has been deleted by admin $admin_id from IP $ip");
            echo "<script>alert('Delete successful. {$affected_rows} rows affected.'); window.location.href = '../{$type}list.php';</script>";
        } else {
            echo "<script>alert('Delete failed. No rows affected.'); window.location.href = '../{$type}list.php';</script>";
        }
        mysqli_stmt_close($stmt);
    } catch (mysqli_sql_exception $e) {
        echo "<script>alert('Delete failed: User still has orders.'); window.location.href = '../{$type}list.php';</script>";
    }
}

// Close the database connection
mysqli_close($conn);
?>