<?php

// Start session
session_start();
include "../sessions/sessiontimeout.php";

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['role'] !== 'admin') {
    header("Location: ../../index.php");
    exit;
}

// Include the config file
$config = include ('config.php');

require_once '../../vendor/autoload.php';
require_once '../../process/log.php';
// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../..');  // Go up one directory to reach the 'src' directory where .env resides
$dotenv->load();
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

use Predis\Client as PredisClient;

// Create database connection
$conn = new mysqli($config['servername'], $config['username'], $config['password'], $config['dbname']);

// Retrieve form data
$admin_email = filter_input(INPUT_POST, 'admin_email', FILTER_SANITIZE_EMAIL);

function display_errorMsg($message)
{
    if (!isset($_SESSION['errorMsg'])) {
        $_SESSION['errorMsg'] = [];
    }
    $_SESSION['errorMsg'][] = $message;

}

$redis = new PredisClient([
    "scheme" => "tcp",
    "host" => "redis",
    "port" => 6379
]);

// Get client IP
$ip = $_SERVER['REMOTE_ADDR'];
$attemptKey = "login_attempts:$ip";
$blockKey = "blocked:$ip";

// Check if IP is currently blocked
if ($redis->get($blockKey)) {
    $delay = 30; // Delay in seconds
    sleep($delay); // Halt script execution to slow down the response
    echo "Access temporarily suspended due to unusual activity.";
    exit;
}

// Increment login attempts
$redis->incr($attemptKey);
$redis->expire($attemptKey, 30); // Expire in 30 seconds

// Check attempts count
$attempts = $redis->get($attemptKey);
if ($attempts > 5) {
    logMessage("application.log", "Failed login attempt for admin $admin_email from IP $ip");
    $redis->set($blockKey, true);
    $redis->expire($blockKey, 600); // Block for 10 minutes
}


// Check connection
if ($conn->connect_error) {
    display_errorMsg('Unable to connect to the service, please try again later.');
}


// Validate Email
if (!filter_var($admin_email, FILTER_VALIDATE_EMAIL)) {
    display_errorMsg('Invalid email format.');
}

// Validate CSRF token
if (!isset($_POST['csrf_token'], $_SESSION['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    display_errorMsg('CSRF token mismatch');
}

// Unset the CSRF token now that it's been checked
unset($_SESSION['csrf_token']);
function sendInvitationEmail($verificationCode, $admin_email)
{
    $mail = new PHPMailer(true);
    try {

        $mail->IsSMTP();

        //        $mail->SMTPDebug = 2;
        $mail->SMTPAuth = TRUE;
        $mail->SMTPSecure = "tls";
        $mail->Port = 587;
        $mail->Host = "smtp.gmail.com";
        $mail->Username = $_ENV['PHPMAIL_USERNAME'];
        $mail->Password = $_ENV['PHPMAIL_PASSWORD'];

        $mail->IsHTML(true);
        $mail->AddAddress($admin_email);
        $mail->SetFrom("mechkeysshop@gmail.com");
        $mail->Subject = 'Get Verified at MechKeys!';
        $mail->Body = "Hello!,\n\n" .
            "Welcome to MechKeys!\n\n" .
            "Thank you for joining us as an admin!\n\n" .
            "We are excited to have you as a part of our community.\n\n" .
            "To complete your registration, please use the verification code below:\n\n" .
            "Verification Code: $verificationCode\n\n" .
            "You can apply to be an admin at Mechkeys with the verification code via the following link https://mechkeys.ddns.net/admin/apply.php .".
            "If you did not sign up for this account, please ignore this email.\n\n" .
            "Best regards,\n" .
            "The MechKeys Team";
        $mail->send();
        // header("location: contact.php#form-details");
    } catch (Exception $e) {
        // header("location: contact.php#form-details");
        display_errorMsg($e->getMessage());
    }
}
function generateVerificationCode($length = 6)
{
    if ($length <= 0) {
        throw new InvalidArgumentException('Length must be a positive integer.');
    }
    $characters = $_ENV['VERIFICATION_CODE_CHARS'];
    $charactersLength = strlen($characters);
    $code = '';

    for ($i = 0; $i < $length; $i++) {
        $code .= $characters[random_int(0, $charactersLength - 1)];
    }

    return $code;
}


if (empty($_SESSION['errorMsg'])) {
    $stmt = $conn->prepare("SELECT * FROM mechkeys.admin WHERE admin_email = ?");
    $stmt->bind_param("s", $admin_email);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        display_errorMsg("Email is already in use.");
        header("Location: ../createadmin.php");

    } else {

        $verification_code = generateVerificationCode(10);
        sendInvitationEmail($verification_code, $admin_email);
        $stmt = $conn->prepare("INSERT INTO mechkeys.admin (admin_email, created_by, reg_code) VALUES (?, ?, ?)");
        $stmt->bind_param("sis", $admin_email, $_SESSION['admin_id'], $verification_code);
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Registration successful. Invitation has been sent.";
            header("Location: ../createadmin.php");

        }

    }
    $stmt->close();
}


// If there are errors, redirect back to registration
if (!empty($_SESSION['errorMsg'])) {
    header("Location: ../createadmin.php");
    exit();
}

// Close the connection
$conn->close();
?>