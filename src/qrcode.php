<?php
session_start();

require 'vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
if (!isset($_SESSION['registration_step'])) {
    header("Location: register.php");
    exit;
}
if ($_SESSION['registration_step'] == 'email_verify') {
    header("Location: verify.php");
    exit;
}
elseif($_SESSION['registration_step'] !== 'qrcode_verify'){
    header("Location:register.php");
    exit;

}

$ga = new PHPGangsta_GoogleAuthenticator();

// Generate a secret key for the user
$secret = $ga->createSecret();

// Generate the QR code URL
$qrCodeUrl = $ga->getQRCodeGoogleUrl('MechKeys', $secret);
$encryption_key = $_ENV['GOOGLE_ENCRYPTION_KEY']; // Use a secure key
$encrypted_secret = openssl_encrypt($secret, 'aes-256-cbc', $encryption_key, 0, $_ENV['GOOGLE_ENCRYPTION_SECRET']);

// Save $secret in the user's record in your database
$_SESSION['GA_secret'] = $encrypted_secret
// Display the QR code URL
?>

<?php
include "sessions/sessiontimeout.php";
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
?>
<html lang="en">
    <head>
        <?php
        include "components/essential.inc.php";
        ?>
        <link rel="stylesheet" href="css/main.css">
        <link rel="stylesheet" href="css/login_reg.css">
    </head>

    <body>
        <?php
        include "components/nav.inc.php";
        ?>
        <main class="container mt-5">
            <div class="login">
                <div class="logincontainer row-cols-3 g-3">
                    <div class="right col-lg-6 col-md-6 col-sm-6 col-12">
                        <div class="login-form">
                        <?php
                            if (isset($_SESSION['errorMsg'])) {
                                echo "<div class='errorMsg'>"; 
                                foreach ($_SESSION['errorMsg'] as $message) {
                                    echo "<p class='error'>" . htmlspecialchars($message) . "</p>";
                                }
                                echo "</div>";
                                unset($_SESSION['errorMsg']); // Clear the error message after displaying it
                            }
                            ?>

                            <h2>Scan this QR code with the Google Authenticator app to enable multi- Factor Authentication!</h2>
                            <h4>Please do not refresh the page after scanning!</h4>
                            <form action="process/qrcode_process.php" method="post">
                                <!-- Include the CSRF token in the form -->
                                <?php echo '<img src="'.$qrCodeUrl.'" />'; ?>

                                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                <p>
                                    <input type="hidden" id="qr_code" name="qr_code" value="1" required>
                                </p>
                                <div id="html_element"></div>
                                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                <p>
                                    <input type="submit" value="I have scanned the QR!">
                                </p>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </main>
        <?php
        include "components/footer.inc.php";
        ?>
    </body>
</html>