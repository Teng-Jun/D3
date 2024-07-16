<?php
include "../sessions/sessiontimeout.php";
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['role'] !== 'admin') {
    header("Location: ../../index.php");
    exit;
}
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
include "components/essential.inc.php";
include "components/nav.inc.php";

//Include the process to fetch profile and order data
include "process/process_profile_order_data.php";

// Check for error messages
$errorMessages = isset($_SESSION['errorMsg']) ? $_SESSION['errorMsg'] : [];
?>

<html lang="en">
    <head>
        <link rel="stylesheet" href="css/main.css">
        <link rel="stylesheet" href="css/profile.css">
        
        <script>
            function togglePasswordFields() {
                var passwordFields = document.getElementById("passwordFields");
                if (document.getElementById("change_password").value === "yes") {
                    passwordFields.style.display = "block";
                } else {
                    passwordFields.style.display = "none";
                }
            }
        </script>
    </head>
    <body>
        <main class="container mt-5">
            <div class="profile-container">
                <div class="profile-header">
                    <h2>Your Profile</h2>
                </div>
                <div class="profile-form">
                    <form action="process/process_profile.php" method="post">
                        <!-- Include the CSRF token in the form -->
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <div class="form-group">
                            <label for="admin_email">Email:</label>
                            <input type="email" id="admin_email" name="admin_email" value="<?php echo htmlspecialchars($admin['admin_email']); ?>" required readonly>
                        </div>

                        <div class="form-group">
                            <label for="change_password">Change Password:</label>
                            <select id="change_password" name="change_password" onchange="togglePasswordFields()">
                                <option value="no">No</option>
                                <option value="yes">Yes</option>
                            </select>
                        </div>

                        <div id="passwordFields" class="form-group">
                            <label for="admin_pwd">New Password:</label>
                            <input type="password" id="admin_pwd" name="admin_pwd" placeholder="Enter new password">
                            <label for="admin_confirm_pwd">Confirm New Password:</label>
                            <input type="password" id="admin_confirm_pwd" name="admin_confirm_pwd" placeholder="Confirm new password">
                        </div>
                        <?php if (!empty($errorMessages)): ?>
                        <div class="error-messages">
                            <?php foreach ($errorMessages as $message): ?>
                                <p><?php echo htmlspecialchars($message); ?></p>
                            <?php endforeach;
                            unset($_SESSION['errorMsg']);
                            ?>
                        </div>
                    <?php endif; ?>
                        <div class="form-group" style="margin-top: 20px;">
                            <input type="submit" value="Update Profile">
                        </div>
                    </form>
                </div>

                <!-- <div class="orders-container">
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
                </div> -->
            </div>
        </main>
        <?php include "components/footer.inc.php"; ?>
    </body>
</html>