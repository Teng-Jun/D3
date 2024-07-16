<?php
        include "components/essential.inc.php";
        session_start();
        
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        ?>

<html lang="en">
    <head>
        <link rel="stylesheet" href="css/main.css">
        <link rel="stylesheet" href="css/login_reg.css">
    </head>
    <script src="https://www.google.com/recaptcha/api.js?render=6LePCAIqAAAAAK_A4_vtPeH80EkH6EIpc8CbMYcy"></script>
    <script>
        grecaptcha.ready(function() {
            grecaptcha.execute('6LePCAIqAAAAAK_A4_vtPeH80EkH6EIpc8CbMYcy', {action: 'register'}).then(function(token) {
                // Add your token to a hidden input
                document.getElementById('recaptchaResponse').value = token;
        });
    });
    </script>
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
                            <p>Start your Management by Logging in!</p>
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
                            <form action="process/process_register.php" method="post">
                                <p>
                                    <label for="admin_email">Email: <span>*</span></label>
                                    <input type="email" id="admin_email" name="admin_email" placeholder="Enter Email" minlength="10" maxlength="30" required>
                                </p>
                                <p>
                                    <label for="admin_pwd">Password: <span>*</span></label>
                                    <input type="password" id="admin_pwd" name="admin_pwd" placeholder="Enter Password" minlength="12" maxlength="70" required>
                                </p>
                                <p>
                                    <label for="admin_confirm_pwd">Confirm Password: <span>*</span></label>
                                    <input type="password" id="admin_confirm_pwd" name="admin_confirm_pwd" placeholder="Confirm Password" minlength="12" maxlength="70" required>
                                </p>
                                <!-- Hidden fields for csrf token, points and join date -->
                                <input type="hidden" name="recaptcha_response" id="recaptchaResponse">
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