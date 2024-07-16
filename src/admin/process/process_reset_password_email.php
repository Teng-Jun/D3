<?php

session_start();

// Include necessary files
try {
    // Include necessary files
    require_once __DIR__ . '/../../vendor/autoload.php';
//    require_once 'log.php';
} catch (Exception $e) {
    echo 'Caught exception: ', $e->getMessage(), "\n";
}

//require_once 'log.php';
// Function to display error messages
function display_errorMsg($message) {
    if (!isset($_SESSION['errorMsg'])) {
        $_SESSION['errorMsg'] = [];
    }
    $_SESSION['errorMsg'][] = $message;
}

// Function to update password
function updatePassword($admin_email, $new_password) {
    // Include the config file
    $config = include('config.php');

    // Create database connection
    $conn = new mysqli($config['servername'], $config['username'], $config['password'], $config['dbname']);

    // Check connection
    if ($conn->connect_error) {
        display_errorMsg('Unable to connect to the service, please try again later.');
        return false;
    }

    // Hash the new password
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

    // Prepare SQL statement to update password
    $stmt = $conn->prepare("UPDATE admin SET admin_password = ? WHERE admin_email = ?");
    $stmt->bind_param("ss", $hashed_password, $admin_email);
    $stmt->execute();

    // Check if password was updated successfully
    if ($stmt->affected_rows > 0) {
        return true;
    } else {
        display_errorMsg('Failed to update password, please try again.');
        return false;
    }

    // Close statement and connection
    $stmt->close();
    $conn->close();
}

// Validate form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Include the config file
    $config = include('config.php');

    // Create database connection
    $conn = new mysqli($config['servername'], $config['username'], $config['password'], $config['dbname']);

    // Check connection
    if ($conn->connect_error) {
        display_errorMsg('Unable to connect to the service, please try again later.');
    }

    // Retrieve and sanitize form data
    $verification_code = filter_input(INPUT_POST, 'verification_code', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $new_password = filter_input(INPUT_POST, 'new_password', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $confirm_password = filter_input(INPUT_POST, 'confirm_password', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $csrf_token = filter_input(INPUT_POST, 'csrf_token', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    // Verify CSRF token
    if (!hash_equals($_SESSION['csrf_token'], $csrf_token)) {
        display_errorMsg('Invalid CSRF token. Please try again.');
        header("Location: ../reset_password_email.php");
        exit();
    }

    // Check if passwords match
    if ($new_password !== $confirm_password) {
        display_errorMsg('Passwords do not match. Please try again.');
        header("Location: ../reset_password_email.php");
        exit();
    }

    // Retrieve session variables
    $admin_email = $_SESSION['reset_email'];

    // Prepare SQL statement to fetch verification code
    $stmt = $conn->prepare("SELECT verification_code FROM admin WHERE admin_email = ?");
    $stmt->bind_param("s", $admin_email);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if a row is returned
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $stored_verification_code = $row['verification_code'];

        // Verify entered verification code
        if ($verification_code !== $stored_verification_code) {
            display_errorMsg('Invalid verification code. Please try again.');
            header("Location: ../reset_password_email.php");
            exit();
        }

        // Update password
        if (updatePassword($admin_email, $new_password)) {
            // Password updated successfully
            // Optionally, clear session variables here
            unset($_SESSION['reset_email']);
            unset($_SESSION['verification_code']);
            display_errorMsg('Successfully updated password');
            header("Location: ../login.php");
            exit();
        } else {
            // Password update failed
            display_errorMsg('Failed to update password. Please try again.');
            header("Location: ../reset_password_email.php");
            exit();
        }
    } else {
        // Handle case where email does not exist or verification code is not found
        display_errorMsg('Email address not found or verification code not valid.');
        header("Location: ../reset_password_email.php");
        exit();
    }
    display_errorMsg('Email address not found or verification code not valid.');
    header("Location: ../reset_password_email.php");
    exit();

    // Close database connection
    $stmt->close();
    $conn->close();
} else {
    // Redirect if form submission method is incorrect
    header("Location: ../reset_password_email.php");
    exit();
}
?>