<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Function to display error messages
function display_errorMsg($message)
{
    if (!isset($_SESSION['errorMsg'])) {
        $_SESSION['errorMsg'] = [];
    }
    $_SESSION['errorMsg'][] = $message;
}

// Function to return JSON response
function jsonResponse($success, $errors = [])
{
    echo json_encode(['success' => $success, 'errors' => $errors]);
    exit;
}

// Check if user is logged in and has customer role
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['role'] !== 'customer') {
    jsonResponse(false, ['Unauthorized access.']);
}

// Include the config file
$config = require_once ('config.php');

// Include log function
require_once 'log.php';

// Get client IP
$ip = $_SERVER['REMOTE_ADDR'];

// Create database connection
$conn = new mysqli($config['servername'], $config['username'], $config['password'], $config['dbname']);

// Check connection
if ($conn->connect_error) {
    jsonResponse(false, ["Unable to connect to the service, please try again later."]);
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

// Check if the request method is POST for profile update
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Retrieve form data
    $customer_fname = filter_input(INPUT_POST, 'customer_fname', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $customer_lname = filter_input(INPUT_POST, 'customer_lname', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $customer_address = filter_input(INPUT_POST, 'customer_address', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $customer_number = filter_input(INPUT_POST, 'customer_number', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $change_password = filter_input(INPUT_POST, 'change_password', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $current_pwd = filter_input(INPUT_POST, 'current_pwd', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $new_pwd = filter_input(INPUT_POST, 'new_pwd', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $confirm_pwd = filter_input(INPUT_POST, 'confirm_pwd', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    // Regex Patterns
    $pattern_name = "/^[a-zA-Z\s'-]+$/";
    $pattern_address = "/^[a-zA-Z0-9\s,.'-]+$/";
    $pattern_number = "/^\d{8}$/";

    // Validate inputs
    if (!preg_match($pattern_name, $customer_fname)) {
        display_errorMsg("First name contains invalid characters.");
    }

    if (!preg_match($pattern_name, $customer_lname)) {
        display_errorMsg("Last name contains invalid characters.");
    }

    if (!preg_match($pattern_address, $customer_address)) {
        display_errorMsg("Address contains invalid characters.");
    }

    if (!preg_match($pattern_number, $customer_number)) {
        display_errorMsg("Phone number must be exactly 8 digits.");
    }

    // Validate current password if changing password
    if ($change_password === "yes") {
        if (!password_verify($current_pwd, $customer['customer_password'])) {
            display_errorMsg("Current password is incorrect.");
        }

        if (empty($new_pwd) || empty($confirm_pwd)) {
            display_errorMsg("Password fields cannot be empty.");
        }

        if (strlen($new_pwd) < 12 || strlen($new_pwd) > 70) {
            display_errorMsg("Password must be at least 12 and below 70 characters long.");
        }

        if ($new_pwd !== $confirm_pwd) {
            $_SESSION['errorMsg'][] = "Passwords do not match.";
            echo '<script>alert("Passwords do not match."); window.location.href = "' . $_SERVER['REQUEST_URI'] . '";</script>';
            exit();
        }
    }

    // Validate CSRF token
    if (!isset($_POST['csrf_token'], $_SESSION['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        // Refresh the page if CSRF token mismatch
        header("Location: " . $_SERVER['REQUEST_URI']);
        exit();
    }

    // Unset the CSRF token now that it's been checked
    unset($_SESSION['csrf_token']);

    // If there are errors, return the errors as JSON response
    if (!empty($_SESSION['errorMsg'])) {
        jsonResponse(false, $_SESSION['errorMsg']);
    }

    // Update the data in the customer table
    if (empty($_SESSION['errorMsg'])) {

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
        jsonResponse(true);
        display_errorMsg("Details has been updated successfully.");

    } else {
        jsonResponse(false, ["Error updating profile: " . $stmt->error]);
        display_errorMsg("Update uncessful. Try again later.");
#
    }
    header("Location: ../profile.php");
    $stmt->close();
    $conn->close();
    exit();
}}
?>