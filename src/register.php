<?php
session_start();
require_once 'vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
?>
<html lang="en">

<head>
    <?php require_once "components/essential.inc.php"; ?>
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/login_reg.css">
    <script src="js/password_strength.js" defer></script>
</head>
<script src="https://www.google.com/recaptcha/api.js?render=<?php echo $_ENV['GOOGLE_CAPTCHA_KEY']; ?>"></script>
<script>
    grecaptcha.ready(function () {
        grecaptcha.execute(<?php echo $_ENV['GOOGLE_CAPTCHA_KEY']; ?>, { action: 'register' }).then(function (token) {
            // Add your token to a hidden input
            document.getElementById('recaptchaResponse').value = token;
        });
    });
</script>


<body>
    <?php
    include_once "components/nav.inc.php";
    ?>
    <main class="container mt-5">
        <div class="login">
            <div class="logincontainer row-cols-3 g-3">
                <div class="left col-lg-6 col-md-6 col-sm-6 col-12">
                    <div class="login-text">
                        <h2>Welcome!!!</h2>
                        <p>Join our community and start your journey with us!</p>
                        <a href="login.php" class="btn">Login</a>
                    </div>
                </div>
                <div class="right col-lg-6 col-md-6 col-sm-6 col-12">
                    <div class="login-form">
                        <h2>Register</h2>
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
                        <form id="registerForm" action="process/process_register.php" method="post">
                            <p>
                                <label for="customer_fname">First Name: <span>*</span></label>
                                <input type="text" id="customer_fname" name="customer_fname"
                                    placeholder="Enter First Name" minlength="3" maxlength="20" required>
                            </p>
                            <p>
                                <label for="customer_lname">Last Name: <span>*</span></label>
                                <input type="text" id="customer_lname" name="customer_lname"
                                    placeholder="Enter Last Name" minlength="3" maxlength="20" required>
                            </p>
                            <p>
                                <label for="customer_email">Email: <span>*</span></label>
                                <input type="email" id="customer_email" name="customer_email" placeholder="Enter Email"
                                    minlength="10" maxlength="40" required>
                            </p>
                            <p>
                                <label for="customer_address">Address: <span>*</span></label>
                                <input type="text" id="customer_address" name="customer_address"
                                    placeholder="Enter Address" minlength="10" maxlength="50" required>
                            </p>
                            <p>
                                <label for="customer_number">Phone Number: <span>*</span></label>
                                <input type="tel" id="customer_number" name="customer_number"
                                    placeholder="Enter Phone Number" minlength="8" maxlength="8" required>
                            </p>
                            <p>
                                <label for="customer_pwd">Password: <span>*</span></label>
                                <input type="password" id="customer_pwd" name="customer_pwd"
                                    placeholder="Enter Password" minlength="12" maxlength="70" required
                                    onkeyup="checkPasswordStrength()">
                            <div id="password-strength-meter" style="width: 100%; height: 5px; background-color: gray;"></div>
                            <div id="password-strength-text" style="color: gray;">Strength Indicator</div>
                            </p>
                            <p>
                                <label for="confirm_pwd">Confirm Password: <span>*</span></label>
                                <input type="password" id="confirm_pwd" name="confirm_pwd"
                                    placeholder="Confirm Password" minlength="12" maxlength="70" required>
                            </p>
                            <!-- Hidden fields for csrf token, points and join date -->
                            <input type="hidden" name="recaptcha_response" id="recaptchaResponse">
                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                            <input type="hidden" name="customer_points" value="0">
                            <input type="hidden" name="customer_join_date" value="<?php echo date('Y-m-d'); ?>">
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
        include_once "components/footer.inc.php";
        ?>
</body>

</html>