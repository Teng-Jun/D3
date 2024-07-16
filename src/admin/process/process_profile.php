<?php
session_start();

//Check if user and has admin role
if ($_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}
require_once "../sessions/sessiontimeout.php";
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['role'] !== 'admin') {
    header("Location: ../../index.php");
    exit;
}

// Include the config file
$config = include('config.php');

// Create database connection
$conn = new mysqli($config['servername'], $config['username'], $config['password'], $config['dbname']);

// function display_errorMsg($message) {
//     if (!isset($_SESSION['errorMsg'])) {
//         $_SESSION['errorMsg'] = [];
//     }
//     $_SESSION['errorMsg'][] = $message;

// }

// Check connection
if ($conn->connect_error) {
    // die("Connection failed: " . $conn->connect_error);
    display_errorMsg("Unable to connect to the service, please try again later.");
}

// Retrieve form data
$admin_id = $_SESSION['admin_id'];
$change_password = filter_input(INPUT_POST, 'change_password', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$admin_pwd = filter_input(INPUT_POST, 'admin_pwd', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$admin_confirm_pwd = filter_input(INPUT_POST, 'admin_confirm_pwd', FILTER_SANITIZE_FULL_SPECIAL_CHARS);


if ($change_password === "yes" && (empty($admin_pwd) || empty($admin_confirm_pwd))) {
    display_errorMsg("Password fields cannot be empty.");
}

// Validate password
if ($change_password === "yes" && strlen($admin_pwd) < 8) {
    display_errorMsg("Password must be at least 8 characters long.");
}

// Validate form data
if ($change_password === "yes" && ($admin_pwd !== $admin_confirm_pwd)) {
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

if ($change_password === "yes") {
    $hashed_password = password_hash($admin_pwd, PASSWORD_DEFAULT);
    $sql = "UPDATE mechkeys.admin SET admin_password = ? WHERE admin_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $hashed_password, $admin_id);
    if ($stmt->execute()) {
        $_SESSION['successMsg'] = "Profile updated successfully.";
    } else {
        display_errorMsg("Error updating profile: " . $stmt->error);
    }

    if ($stmt->execute()) {
        $_SESSION['successMsg'] = "Profile updated successfully.";
    } else {
        display_errorMsg("Error updating profile: " . $stmt->error);
    }
    $stmt->close(); // Make sure to close the statement
} 

// Close the connection
$conn->close();

header("Location: ../profile.php");
?>