<?php
// Start session
session_start();

// Include the config file
$config = include ('config.php');

require_once '../../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../..');  // Go up one directory to reach the 'src' directory where .env resides
$dotenv->load();
use Predis\Client as PredisClient;

if (!$_SERVER["REQUEST_METHOD"] === "POST" || !isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    display_errorMsg('Please try again');
    header("Location: ../apply.php");
    exit;

}
function display_errorMsg($message)
{
    if (!isset($_SESSION['errorMsg'])) {
        $_SESSION['errorMsg'] = [];
    }
    $_SESSION['errorMsg'][] = $message;

}

// Create database connection
$conn = new mysqli($config['servername'], $config['username'], $config['password'], $config['dbname']);

// $secret = $_ENV['GOOGLE_CAPTCHA_KEY'];  // Replace with your secret key
// $response = $_POST['recaptcha_response'];
// $verify = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret={$secret}&response={$response}");
// $responseData = json_decode($verify);

// Retrieve and sanitize form data
$admin_email = filter_input(INPUT_POST, 'admin_email', FILTER_SANITIZE_EMAIL);
$admin_pwd = filter_input(INPUT_POST, 'admin_pwd', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$admin_confirm_pwd = filter_input(INPUT_POST, 'admin_confirm_pwd', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$admin_code = filter_input(INPUT_POST, 'admin_code', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$qrcode = filter_input(INPUT_POST, 'qr_code', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
if ($qrcode !== '1') {
    display_errorMsg('Error, please try again.');
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
    logMessage("application.log", "Failed register attempt for admin $admin_email from IP $ip");
    $redis->set($blockKey, true);
    $redis->expire($blockKey, 600); // Block for 10 minutes
}


// Check connection
if ($conn->connect_error) {
    display_errorMsg("Unable to connect to the service, please try again later.");
}

// Validate Email
if (!filter_var($admin_email, FILTER_VALIDATE_EMAIL)) {
    display_errorMsg("Invalid email format.");
}

// Validate password
if (strlen($admin_pwd) < 12 || strlen($admin_email) > 70) {
    display_errorMsg("Password must be at least 8 characters long.");
}



// Check if passwords match
if ($admin_pwd !== $admin_confirm_pwd) {
    display_errorMsg("Passwords do not match.");
}
// Check if passwords match
if ($admin_pwd !== $admin_confirm_pwd) {
    display_errorMsg("Passwords do not match.");
}

if (preg_match('/[^a-zA-Z0-9]/', $admin_code)) {
    display_errorMsg("Please try again");
}

// Check for existing email
if (empty($_SESSION['errorMsg'])) {
    $stmt = $conn->prepare("SELECT * FROM mechkeys.admin WHERE admin_email = ?");
    $stmt->bind_param("s", $admin_email);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
    } else {
        display_errorMsg("Please try again.");
        header("Location: ../apply.php");
        exit();
    }
    $stmt->close();
}


// Unset CSRF token after checking it
unset($_SESSION['csrf_token']);

// if ($responseData->success && $responseData->score < 0.5) {  // Choose your threshold
//     display_errorMsg('reCAPTCHA verification failed. Are you a robot?');
// }

if (empty($_SESSION['errorMsg'])) {

    if ($stmt = $conn->prepare("SELECT reg_code FROM mechkeys.admin WHERE admin_email = ?")) {
        $stmt->bind_param("s", $admin_email);
        $stmt->execute();
        $result = $stmt->get_result();

        // Check if user exists
        if ($row = $result->fetch_assoc()) {
            // Verify password
            if ($admin_code == $row['reg_code']) {
                $hashed_pwd = password_hash($admin_pwd, PASSWORD_DEFAULT);
                $admin_gacode = $_SESSION['GA_secret'];

                // Set session variables and redirect to a secure page
                if ($stmt = $conn->prepare("UPDATE mechkeys.admin SET admin_password = ?, ga_code = ? WHERE admin_email = ?")) {
                    $stmt->bind_param("sss", $hashed_pwd, $admin_gacode,  $admin_email);
                    $stmt->execute();

                    if ($stmt->affected_rows > 0) {
                        // echo "Customer details updated successfully.";
                        display_errorMsg('Admin details updated successfully.');
                        header("Location: ../login.php");
                        $stmt->close();
                        exit();

                    } else {
                        // echo "No records updated";
                        display_errorMsg('Something went wrong, please try again later.');
                        header("Location: ../apply.php");
                        $stmt->close();
                        exit();

                    }
                 } else {
                    // echo "Error preparing statement: " . $conn->error;
                    display_errorMsg('Something went wrong, please try again later.');
                    header("Location: ../apply.php");
                    exit();
                }

            } else {
                // Handle when password is incorrect
                display_errorMsg('Incorrect email or password');
            }
        } else {
            // Handle no user found
            // echo $_SESSION['$customer_email'];
            // echo $customer_code;
            // echo "Error preparing statement: (" . $conn->errno . ") " . $conn->error;
            display_errorMsg('Incorrect token');
            exit();
        }
        // Close the statement
        $stmt->close();
    }
}
// If there are errors, redirect back to registration
if (!empty($_SESSION['errorMsg'])) {
    header("Location: ../apply.php");
    exit();
}

// Close the connection
$conn->close();
?>