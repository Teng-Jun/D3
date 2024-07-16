<?php
session_start();

//Check if user and has customer role
if ($_SESSION['role'] !== 'customer') {
    header('Location: login.php');
    exit;
}

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

// Include the config file
$config = include('config.php');

// Create database connection
$conn = new mysqli($config['servername'], $config['username'], $config['password'], $config['dbname']);

function display_errorMsg($message) {
    if (!isset($_SESSION['errorMsg'])) {
        $_SESSION['errorMsg'] = [];
    }
    $_SESSION['errorMsg'][] = $message;

}

// Check connection
if ($conn->connect_error) {
    // die("Connection failed: " . $conn->connect_error);
    display_errorMsg("Unable to connect to the service, please try again later.");
}

// Retrieve form data
$customer_id = $_SESSION['customer_id'];
$customer_fname = filter_input(INPUT_POST, 'customer_fname', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$customer_lname = filter_input(INPUT_POST, 'customer_lname', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$customer_address = filter_input(INPUT_POST, 'customer_address', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$customer_number = filter_input(INPUT_POST, 'customer_number', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$change_password = filter_input(INPUT_POST, 'change_password', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$customer_pwd = filter_input(INPUT_POST, 'customer_pwd', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$confirm_pwd = filter_input(INPUT_POST, 'confirm_pwd', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

// Regex Patterns
$pattern_name = "/^[a-zA-Z\s'-]+$/";;
$pattern_address = "/^[a-zA-Z0-9\s,.'-]+$/";
$pattern_number = "/^\d{8}$/";

// Validate First Name
if (!preg_match($pattern_name, $customer_fname)) {
    // Handle invalid input
    display_errorMsg("First name contains invalid characters.");
}

// Validate Last Name
if (!preg_match($pattern_name, $customer_lname)) {
    // Handle invalid input
    display_errorMsg("Last name contains invalid characters.");
}

// Validate Address
if (!preg_match($pattern_address, $customer_address)) {
    display_errorMsg("Address contains invalid characters.");
}

// Validate Phone Number
if (!preg_match($pattern_number, $customer_number)) {
    display_errorMsg("Phone number must be exactly 8 digits.");
}

if ($change_password === "yes" && (empty($new_pwd) || empty($confirm_pwd))) {
    display_errorMsg("Password fields cannot be empty.");
}

// Validate password
if ($change_password === "yes" && strlen($new_pwd) < 12 || strlen($new_pwd) > 70) {
    display_errorMsg("Password must be at least 12 characters long.");
}

// Validate form data
if ($change_password === "yes" && ($new_pwd !== $confirm_pwd)) {
    display_errorMsg( "Passwords do not match.");
}

// Validate CSRF token
if (!isset($_POST['csrf_token'], $_SESSION['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    display_errorMsg('CSRF token mismatch');
}

// Unset the CSRF token now that it's been checked
unset($_SESSION['csrf_token']);

// If there are errors, redirect back to registration
if (!empty($_SESSION['errorMsg'])) {
    header("Location: ../profile.php");
    exit();
}

// Update the data in the customer table
if ($change_password === "yes") {
    $hashed_password = password_hash($new_pwd, PASSWORD_DEFAULT);
    $sql = "UPDATE mechkeys.customer SET customer_fname = ?, customer_lname = ?, customer_address = ?, customer_number = ?, customer_password = ? WHERE customer_id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        throw new Exception("Error preparing statement: " . $conn->error);
    }
    $stmt->bind_param("sssssi", $customer_fname, $customer_lname, $customer_address, $customer_number, $hashed_password, $customer_id);
} else {
    $sql = "UPDATE mechkeys.customer SET customer_fname = ?, customer_lname = ?, customer_address = ?, customer_number = ? WHERE customer_id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        throw new Exception("Error preparing statement: " . $conn->error);
    }
    $stmt->bind_param("ssssi", $customer_fname, $customer_lname, $customer_address, $customer_number, $customer_id);
}

if ($stmt->execute()) {
    $_SESSION['successMsg'] = "Profile updated successfully. Thank you";
} else {
    display_errorMsg("Error updating profile: " . $stmt->error);
}

header("Location: ../profile.php");
$stmt->close();
$conn->close();
?>