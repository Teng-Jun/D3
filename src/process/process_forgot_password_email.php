<?php
session_start();

// Include necessary files
require_once '../vendor/autoload.php';
require_once 'log.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');  // Points to the 'src' directory
$dotenv->load();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Function to display error messages
function display_errorMsg($message)
{
    if (!isset($_SESSION['errorMsg'])) {
        $_SESSION['errorMsg'] = [];
    }
    $_SESSION['errorMsg'][] = $message;
}

// Function to send verification code email
function sendVerificationCodeEmail($verificationCode, $customer_email, $customer_fname)
{
    $mail = new PHPMailer(true);
    try {
        // SMTP configuration
        $mail->IsSMTP();
        $mail->SMTPAuth = true;
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;
        $mail->Host = 'smtp.gmail.com';
        $mail->Username = $_ENV['PHPMAIL_USERNAME'];
        $mail->Password = $_ENV['PHPMAIL_PASSWORD'];

        // Email content
        $mail->IsHTML(true);
        $mail->AddAddress($customer_email);
        $mail->SetFrom('keyboarderweb@gmail.com');
        $mail->Subject = 'Password Reset Verification Code';
        $mail->Body = "Dear $customer_fname,<br><br>"
                    . "You have requested to reset your password on MechKeys.<br><br>"
                    . "Please use the following verification code to proceed with resetting your password:<br><br>"
                    . "<strong>Verification Code: $verificationCode</strong><br><br>"
                    . "If you did not initiate this request, please ignore this email.<br><br>"
                    . "Best regards,<br>"
                    . "The MechKeys Team";

        // Send email
        $mail->send();
        // echo "Verification code sent to your email.";
    } catch (Exception $e) {
        display_errorMsg($e->getMessage());
    }
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
    $customer_email = filter_input(INPUT_POST, 'customer_email', FILTER_SANITIZE_EMAIL);

    // Prepare SQL statement to check if email exists
    $stmt = $conn->prepare("SELECT customer_fname FROM mechkeys.customer WHERE customer_email = ?");
    $stmt->bind_param("s", $customer_email);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if user exists
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $customer_fname = $row['customer_fname'];

        // Generate unique verification code
        $verificationCode = substr(str_shuffle($_ENV['VERIFICATION_CODE_CHARS']), 0, 6); // Generate a 6-character alphanumeric code

        // Store verification code in database
        $stmt = $conn->prepare("UPDATE mechkeys.customer SET verification_code = ? WHERE customer_email = ?");
        $stmt->bind_param("ss", $verificationCode, $customer_email);
        $stmt->execute();

        // Send verification code email
        sendVerificationCodeEmail($verificationCode, $customer_email, $customer_fname);

        // Redirect to verification page
        $stmt->close();
        $conn->close();
    
        $_SESSION['reset_email'] = $customer_email; // Store email in session for verification
        header("Location: ../reset_password_email.php");
        exit();
    } else {
        // Handle case where email does not exist
        $stmt->close();
        $conn->close();
    
        display_errorMsg('Email address not found.');
        header("Location: ../forgot_password.php");
        exit();
    }

    // Close database connection
    $stmt->close();
    $conn->close();
} else {
    // Redirect if form submission method is incorrect
    header("Location: ../forgot_password.php");
    exit();
}
?>