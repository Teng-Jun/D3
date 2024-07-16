<?php

// Include the config file
$config = include('config.php');
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['role'] !== 'admin') {
    header("Location: ../../index.php");
    exit;
}

// Create database connection
$conn = new mysqli($config['servername'], $config['username'], $config['password'], $config['dbname']);

// Check connection
if ($conn->connect_error) {
    $_SESSION['errorMsg'][] = "Connection failed: " . $conn->connect_error;
    header("Location: ../profile.php"); // Redirect to an error page or handle the error as needed
    exit();
}

// Fetch admin data using customer_id from session
$admin_id = $_SESSION['admin_id'];
$sql = "SELECT * FROM mechkeys.admin WHERE admin_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc();

// Close the statements and connection
$stmt->close();
$conn->close();
?>
