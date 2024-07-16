<?php
session_start();
include "components/essential.inc.php";
include_once "../vendor/autoload.php";

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');  // Go up one directory to reach the 'src' directory where .env resides
$dotenv->load();

$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
require '../vendor/autoload.php';
$ga = new PHPGangsta_GoogleAuthenticator();

// Generate a secret key for the user
$secret = $ga->createSecret();

// Generate the QR code URL
$qrCodeUrl = $ga->getQRCodeGoogleUrl('MechKeys', $secret);
$encryption_key = $_ENV['GOOGLE_ENCRYPTION_KEY'];; // Use a secure key
$encrypted_secret = openssl_encrypt($secret, 'aes-256-cbc', $encryption_key, 0, '1234567890123456');

// Save $secret in the user's record in your database
$_SESSION['GA_secret'] = $encrypted_secret
    // Display the QR code URL

?>
<html lang="en">

<head>
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
                <div class="left col-lg-6 col-md-6 col-sm-6 col-12">
                    <div class="login-text">
                        <h2>Welcome!</h2>
                        <p>Start your Management registering with the code sent to your email!</p>
                        <a href="login.php" class="btn">Login</a>
                    </div>
                </div>
                <div class="right col-lg-6 col-md-6 col-sm-6 col-12">
                    <div class="login-form">
                        <h2>Apply to become an Admin!</h2>
                        <!-- Display error messages if there are any -->
                        <?php
                        if (isset($_SESSION['errorMsg']) && is_array($_SESSION['errorMsg']) && count($_SESSION['errorMsg']) > 0) {
                            echo '<div class="errorMsg">';
                            foreach ($_SESSION['errorMsg'] as $message) {
                                echo "<p class='error'>" . htmlspecialchars($message) . "</p>";
                            }
                            // Clear the error messages after displaying
                            unset($_SESSION['errorMsg']);
                            echo '</div>';
                        }
                        ?>
                        <form action="process/process_apply.php" method="post">
                            <p>
                                <label for="admin_email">Email: <span>*</span></label>
                                <input type="email" id="admin_email" name="admin_email" placeholder="Enter Email"
                                    minlength="10" maxlength="30" required>
                            </p>
                            <p>
                                <label for="admin_pwd">Password: <span>*</span></label>
                                <input type="password" id="admin_pwd" name="admin_pwd" placeholder="Enter Password"
                                    minlength="12" maxlength="70" required>
                            </p>
                            <p>
                                <label for="admin_confirm_pwd">Confirm Password: <span>*</span></label>
                                <input type="password" id="admin_confirm_pwd" name="admin_confirm_pwd"
                                    placeholder="Confirm Password" minlength="12" maxlength="70" required>
                            </p>
                            <p>
                                <label for="admin_code">Code: <span>*</span></label>
                                <input type="text" id="admin_code" name="admin_code"
                                    placeholder="Enter the Code sent to your email" required>
                            </p>
                            <br>
                            <strong style="">Please scan the QR Code below with your google authenticator app. Do not
                                refresh the page after scanning.</strong>
                            <br>
                            <?php echo '<img src="' . $qrCodeUrl . '" />'; ?>
                            <p>
                                <input type="hidden" id="qr_code" name="qr_code" value="1" required>
                            </p>

                            <!-- Hidden fields for csrf token, points and join date -->
                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                            <p>
                                <input type="submit" value="Register">
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