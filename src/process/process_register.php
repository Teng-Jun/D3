<?php
// Start session
session_start();
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);
require_once 'log.php';
require_once '../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');  // Points to the 'src' directory
$dotenv->load();
use Predis\Client as PredisClient;

function display_errorMsg($message)
{
    if (!isset($_SESSION['errorMsg'])) {
        $_SESSION['errorMsg'] = [];
    }
    $_SESSION['errorMsg'][] = $message;

}

$customer_email = filter_input(INPUT_POST, 'customer_email', FILTER_SANITIZE_EMAIL);

if (!$_SERVER["REQUEST_METHOD"] == "POST" || !isset($_POST['csrf_token'], $_SESSION['csrf_token']) || $_POST['csrf_token'] != $_SESSION['csrf_token']) {
    display_errorMsg('Please try again');
    header("Location: ../register.php");
    exit;

} else {
    // Include the config file
    $config = require_once ('config.php');
    
    $redis = new PredisClient([
        "scheme" => "tcp",
        "host"   => "redis",
        "port"   => 6379
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
        logMessage("application.log", "Failed register attempt for user $customer_email from IP $ip");      
        $redis->set($blockKey, true);
        $redis->expire($blockKey, 600); // Block for 10 minutes
    }

    unset($_SESSION['csrf_token']);

    // Create database connection
    $conn = new mysqli($config['servername'], $config['username'], $config['password'], $config['dbname']);

    // Check connection
    if ($conn->connect_error) {
        display_errorMsg("Unable to connect to the service, please try again later.");
        header("Location: ../register.php");
        exit;
    }


    $secret = $_ENV['GOOGLE_CAPTCHA_KEY'];  // Replace with your secret key
    $response = $_POST['recaptcha_response'];
    $verify = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret={$secret}&response={$response}");
    $responseData = json_decode($verify);


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

    function sendVerificationEmail($verificationCode, $customer_email, $customer_fname)
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
            $mail->AddAddress($customer_email);
            $mail->SetFrom("mechkeysshop@gmail.com");
            $mail->Subject = 'Get Verified at MechKeys!';
            $mail->Body = "Dear $customer_fname,\n

            Welcome to MechKeys!\n\n

            Thank you for joining us. We are excited to have you as a part of our community.\n

            To complete your registration, please use the verification code below:\n

            Verification Code: $verificationCode\n

            If you did not sign up for this account, please ignore this email.\n\n

            Best regards,\n
            The MechKeys Team
            ";



            $mail->send();
            // header("location: contact.php#form-details");
        } catch (Exception $e) {
            // header("location: contact.php#form-details");
            display_errorMsg($e->getMessage());
        }
    }

    // Retrieve and sanitize form data
    $customer_fname = filter_input(INPUT_POST, 'customer_fname', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $customer_lname = filter_input(INPUT_POST, 'customer_lname', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $customer_address = filter_input(INPUT_POST, 'customer_address', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $customer_number = filter_input(INPUT_POST, 'customer_number', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $customer_pwd = filter_input(INPUT_POST, 'customer_pwd', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $confirm_pwd = filter_input(INPUT_POST, 'confirm_pwd', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $customer_points = filter_input(INPUT_POST, 'customer_points', FILTER_SANITIZE_NUMBER_INT);
    $customer_join_date = filter_input(INPUT_POST, 'customer_join_date', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    // Regex Patterns
    $pattern_name = "/^[a-zA-Z\s'-]+$/";
    $pattern_address = "/^[a-zA-Z0-9\s,.'-]+$/";
    $pattern_number = "/^\d{8}$/";

    // Validate First Name
    if (strlen($customer_fname) < 3 || strlen($customer_fname) > 20) {
        display_errorMsg("First name must be between 3 and 20 characters long.");
    } elseif (!preg_match($pattern_name, $customer_fname)) {
        display_errorMsg("First name contains invalid characters.");
    }

    // Validate Last Name
    if (strlen($customer_lname) < 3 || strlen($customer_lname) > 20) {
        display_errorMsg("Last name must be between 3 and 20 characters long.");
    } elseif (!preg_match($pattern_name, $customer_lname)) {
        display_errorMsg("Last name contains invalid characters.");
    }

    // Validate Email
    if (!filter_var($customer_email, FILTER_VALIDATE_EMAIL)) {
        display_errorMsg("Invalid email format.");
    } elseif (strlen($customer_email) < 10 || strlen($customer_email) > 50) {
        display_errorMsg("Email must be between 10 and 50 characters long.");
    }

    // Validate Address
    if (strlen($customer_address) < 10 || strlen($customer_address) > 40) {
        display_errorMsg("Address must be between 10 and 40 characters long.");
    } elseif (!preg_match($pattern_address, $customer_address)) {
        display_errorMsg("Address contains invalid characters.");
    }

    // Validate Phone Number
    if (!preg_match($pattern_number, $customer_number)) {
        display_errorMsg("Phone number must be exactly 8 digits.");
    }

    // Validate password
    if (strlen($customer_pwd) < 12 || strlen($customer_pwd) > 70) {
        display_errorMsg("Password must be at least 12 characters long.");
    }

    // Check if passwords match
    if ($customer_pwd !== $confirm_pwd) {
        display_errorMsg("Passwords do not match.");
    }

    // Check for existing email
    if (empty($_SESSION['errorMsg'])) {
        $stmt = $conn->prepare("SELECT * FROM mechkeys.customer WHERE customer_email = ?");
        $stmt->bind_param("s", $customer_email);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            display_errorMsg("Email is already in use.");
        }
        $stmt->close();
    }


    if ($responseData->success && $responseData->score < 0.5) {  // Choose your threshold
        display_errorMsg('reCAPTCHA verification failed. Are you a robot?');
    }

    // Proceed with registration if no errors
    if (empty($_SESSION['errorMsg'])) {
        $hashed_pwd = password_hash($customer_pwd, PASSWORD_DEFAULT);

        $code = generateVerificationCode(6);
        sendVerificationEmail($code, $customer_email, $customer_fname);
        $validated = 0;
        $stmt = $conn->prepare("INSERT INTO mechkeys.customer (customer_fname, customer_lname, customer_email, customer_address, customer_number, customer_password, customer_points, customer_joindate, customer_verification, customer_code) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssisssis", $customer_fname, $customer_lname, $customer_email, $customer_address, $customer_number, $hashed_pwd, $customer_points, $customer_join_date, $validated, $code);
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Registration successful. You can now log in.";
            $_SESSION['customer_email'] = $customer_email;
            $_SESSION['registration_step'] = 'email_verify';
            header("Location: ../verify.php");
            exit();
        } else {
            display_errorMsg("Registration failed, please try again later.");
            
        }
        $stmt->close();
        $conn->close();

    }

    // If there are errors, redirect back to registration
    elseif (!empty($_SESSION['errorMsg'])) {
        header("Location: ../register.php");
        exit();
    }

    else{
        header("Location: ../register.php");
        exit();
    }

    // Close the connection
}
?>