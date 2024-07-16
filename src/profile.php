<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
include "components/essential.inc.php";
include "components/nav.inc.php";

// Include the process to fetch profile and order data and handle updates
include "process/process_profile_combined.php";

// Check for error messages
$errorMessages = isset($_SESSION['errorMsg']) ? $_SESSION['errorMsg'] : [];
?>

<html lang="en">
    <head>
        <link rel="stylesheet" href="css/main.css">
        <link rel="stylesheet" href="css/profile.css">
        <script src="js/profile.js" defer></script> <!-- Include the profile.js script -->
    </head>
    <body>
        <main class="container mt-5">
            <div class="profile-container">
                <div class="profile-header">
                    <h2>Your Profile</h2>
                </div>
                <div class="profile-form">
                    <form id="profileForm">
                        <!-- Include the CSRF token in the form -->
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <div class="form-group">
                            <label for="customer_fname">First Name:</label>
                            <input type="text" id="customer_fname" name="customer_fname" value="<?php echo htmlspecialchars($customer['customer_fname']); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="customer_lname">Last Name:</label>
                            <input type="text" id="customer_lname" name="customer_lname" value="<?php echo htmlspecialchars($customer['customer_lname']); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="customer_email">Email:</label>
                            <input type="email" id="customer_email" name="customer_email" value="<?php echo htmlspecialchars($customer['customer_email']); ?>" required readonly>
                        </div>

                        <div class="form-group">
                            <label for="customer_address">Address:</label>
                            <input type="text" id="customer_address" name="customer_address" value="<?php echo htmlspecialchars($customer['customer_address']); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="customer_number">Phone Number:</label>
                            <input type="tel" id="customer_number" name="customer_number" value="<?php echo htmlspecialchars($customer['customer_number']); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="change_password">Change Password:</label>
                            <select id="change_password" name="change_password" onchange="togglePasswordFields()">
                                <option value="no">No</option>
                                <option value="yes">Yes</option>
                            </select>
                        </div>

                        <div id="passwordFields" class="form-group" style="display: none;">
                            <label for="current_pwd">Current Password:</label>
                            <input type="password" id="current_pwd" name="current_pwd" placeholder="Enter current password">
                            <label for="new_pwd">New Password:</label>
                            <input type="password" id="new_pwd" name="new_pwd" placeholder="Enter new password" onkeyup="checkPasswordStrength()">
                            <div id="password-strength-meter" style="width: 100%; height: 5px; background-color: gray;"></div>
                            <div id="password-strength-text" style="color: gray;">Strength Indicator</div>
                            <label for="confirm_pwd">Confirm New Password:</label>
                            <input type="password" id="confirm_pwd" name="confirm_pwd" placeholder="Confirm new password">
                        </div>
                        <div class="error-messages">
                        <?php if (!empty($errorMessages)): ?>
                            <?php foreach ($errorMessages as $message): ?>
                                <p><?php echo htmlspecialchars($message); ?></p>
                            <?php endforeach;
                            unset($_SESSION['errorMsg']);
                            ?>
                        <?php endif; ?>
                    </div>
                        <div class="form-group" style="margin-top: 20px;">
                            <input type="submit" value="Update Profile">
                        </div>
                    </form>
                </div>

                <div class="orders-container">
                    <h2>Your Orders</h2>
                    <?php if (count($orders) > 0): ?>
                        <table class="orders-table">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Product ID</th>
                                    <th>Quantity</th>
                                    <th>Tracking No.</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orders as $order): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($order['order_id']); ?></td>
                                        <td><?php echo htmlspecialchars($order['product_id']); ?></td>
                                        <td><?php echo htmlspecialchars($order['order_quantity']); ?></td>
                                        <td><?php echo htmlspecialchars($order['order_tracking_no']); ?></td>
                                        <td><?php echo htmlspecialchars($order['order_status']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p>No orders found.</p>
                    <?php endif; ?>
                </div>
            </div>
        </main>
        <?php include "components/footer.inc.php"; ?>
    </body>
</html>