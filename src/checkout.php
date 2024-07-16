<?php
include "sessions/sessiontimeout.php";
if (!isset($_SESSION['checkout_step']) || !$_SESSION['checkout_step'] == 'checkout') {

    if (!$_SERVER["REQUEST_METHOD"] === "POST" || !isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        header("Location: cart.php");
        exit;

    }
}
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
?>

<html lang="en">

<head>
    <?php
    include "components/essential.inc.php";
    ?>
    <link rel="stylesheet" href="css/index.css">
    <link rel="stylesheet" href="css/main.css">
    <style>
        .form-container {
            background-color: #f2f2f2;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            width: 50%;
            margin: auto;
            margin-top: 20px;
        }

        .form-input {
            width: 100%;
            margin-bottom: 20px;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 3px;
        }

        .form-row {
            display: flex;
            flex-wrap: wrap;
            margin: 0 -16px;
        }

        .form-col-50 {
            flex: 50%;
            padding: 0 16px;
        }

        .form-icon-container {
            margin-bottom: 20px;
            padding: 7px 0;
            font-size: 24px;
        }

        .form-btn {
            background-color: #4CAF50;
            color: white;
            padding: 12px;
            margin: 10px 0;
            border: none;
            width: 100%;
            border-radius: 3px;
            cursor: pointer;
            font-size: 17px;
        }

        .form-btn:hover {
            background-color: #45a049;
        }

        .form-label {
            display: block;
            margin-bottom: 10px;
        }
    </style>
</head>

<body>
    <?php
    include "components/nav.inc.php";
    ?>
    <main>
        <br>
        <div class="form-container">
            <?php 
            $_SESSION['checkout_step'] = 'checkout';
            if (isset($_SESSION['errorMsg'])) {
                echo "<div class='errorMsg'>";
                foreach ($_SESSION['errorMsg'] as $message) {
                    echo "<p class='error'>" . htmlspecialchars($message) . "</p>";
                }
                echo "</div>";
                unset($_SESSION['errorMsg']); // Clear the error message after displaying it
            }
            ?>


            <form action="process/payment_process.php" method="POST">
                <div class="form-row">
                    <div class="form-col-50">
                        <h3>Billing Address</h3>
                        <label class="form-label" for="fname"><i class="fa fa-user"></i> Full Name</label>
                        <input class="form-input" type="text" id="fname" name="fname" placeholder="John M. Doe" minlength="6" maxlength="40"
                            required>
                        <label class="form-label" for="email"><i class="fa fa-envelope"></i> Email</label>
                        <input class="form-input" type="text" id="email" name="email" placeholder="john@example.com" minlength="10" maxlength="30"
                            required>
                        <label class="form-label" for="adr"><i class="fa fa-address-card-o"></i> Address</label>
                        <input class="form-input" type="text" id="adr" name="adr" placeholder="542 W. 15th Street" minlength="10" maxlength="30"
                            required>
                        <label class="form-label" for="city"><i class="fa fa-institution"></i> City</label>
                        <input class="form-input" type="text" id="city" name="city" placeholder="New York" minlength="3" maxlength="30" required>
                        <div class="form-row">
                            <div class="form-col-50">
                                <label class="form-label" for="state">State</label>
                                <input class="form-input" type="text" id="state" name="state" placeholder="NY" minlength="3" maxlength="30">
                            </div>
                            <div class="form-col-50">
                                <label class="form-label" for="zip">Zip</label>
                                <input class="form-input" type="number" id="zip" name="zip" placeholder="100001" minlength="6" maxlength="6">
                            </div>
                        </div>
                    </div>
                    <div class="form-col-50">
                        <h3>Payment</h3>
                        <label class="form-label" for="fname">Accepted Cards</label>
                        <div class="form-icon-container">
                            <i class="fa fa-cc-visa" style="color:navy;"></i>
                            <i class="fa fa-cc-mastercard" style="color:orange;"></i>
                        </div>
                        <label class="form-label" for="cname">Name on Card</label>
                        <input class="form-input" type="text" id="cname" name="cname" placeholder="John More Doe"
                            required>
                        <label class="form-label" for="cnum">Card number</label>
                        <input class="form-input" type="text" id="cnum" name="cnumber" placeholder="1111-2222-3333-4444"
                            required>
                        <div class="form-row">
                            <div class="form-col-50">
                                <label class="form-label" for="cexpire">Card Expire</label>
                                <input class="form-input" type="text" id="cexpire" name="cexpire" placeholder="MM/YY"
                                    required>
                            </div>
                            <div class="form-col-50">
                                <label class="form-label" for="cvv">CVV</label>
                                <input class="form-input" type="text" id="cvv" name="cvv" placeholder="352">
                            </div>
                        </div>
                    </div>
                </div>
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <label>
                    <input type="checkbox" name="sameadr"> Shipping address same as billing
                </label>
                <input type="submit" value="Continue to checkout" class="form-btn">
            </form>
        </div>
    </main>
    <?php
    include "components/footer.inc.php";
    ?>
</body>
<script defer src="js/indexcontent.js"></script>

</html>