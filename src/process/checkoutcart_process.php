<?php
session_start();
require_once "../sessions/sessiontimeout.php";
require_once "../vendor/autoload.php";
use PHPMailer\PHPMailer\PHPMailer;


if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: ../login.php");
    exit;
}
if ( $_SESSION['checkout_step'] !== 'finish') {
    header("Location: ../index.php");
    exit;

} else {


    require '../PHPMailer/src/Exception.php';
    require '../PHPMailer/src/PHPMailer.php';
    require '../PHPMailer/src/SMTP.php';


    function CheckOutCart()
    {
        $table_name = 'product';
        $t = time();
        $date = date("YmdhisA", $t);

        if ($_SESSION['customer_id']) {
            foreach ($_SESSION['cart'] as $productId => $value) {
                // echo "Processing product ID: $productId, Quantity: {$value['qty']}<br>";

                // Calculate the remaining quantity for this product
                $remainqty = Get_ProductQty($productId, $value['qty'], $table_name);
                // echo "Remaining quantity: $remainqty<br>";
                // Update the product quantity in the database
                Update_ProductQty($productId, $remainqty);

                // echo "Product ID: $productId updated with quantity: $remainqty<br>";
                Order_add($productId, $value['qty'], $date);
            }
            sendEmail($date);
            unset($_SESSION['cart']);
        } else {
            header("Location: ../profile.php");
        }
    }

    function Get_ProductQty($key, $qty, $table_name)
    {
        // Include the configuration file
        $config = require 'config.php';

        // Create a new mysqli object with the configuration parameters
        $conn = new mysqli(
            $config['servername'],
            $config['username'],
            $config['password'],
            $config['dbname']
        );

        $remainqty = "";

        if ($conn->connect_error) {
            $errorMsg = "Connection failed: " . $conn->connect_error;
            echo ($errorMsg);
        } else {
            // Prepare the statement
            $stmt = mysqli_prepare($conn, "SELECT * FROM $table_name WHERE $table_name.product_id = $key");

            // Execute the statement
            mysqli_stmt_execute($stmt);

            // Get the result set
            $result = mysqli_stmt_get_result($stmt);

            // Fetch one row
            $row = mysqli_fetch_assoc($result);
            if ($row) {
                // Calculate remaining quantity
                $remainqty = $row[$table_name . '_quantity'] - $qty;
            } else {
                // Handle case where no row is found for the given product ID
                $errorMsg = "No product found for ID: $key";
                echo ($errorMsg);
            }

            // Close connection
            mysqli_stmt_close($stmt);
            mysqli_close($conn);

            return $remainqty;
        }
    }

    function Update_ProductQty($key, $remainqty)
    {
        // Include the configuration file
        $config = require 'config.php';

        // Create a new mysqli object with the configuration parameters
        $conn = new mysqli(
            $config['servername'],
            $config['username'],
            $config['password'],
            $config['dbname']
        );

        if ($conn->connect_error) {
            $errorMsg = "Connection failed: " . $conn->connect_error;
            echo ($errorMsg);
        } else {
            // Prepare the statement
            $stmt = mysqli_prepare($conn, "UPDATE product SET product_quantity = $remainqty WHERE product_id = $key");

            // Execute the statement
            mysqli_stmt_execute($stmt);

            // Close connection
            mysqli_stmt_close($stmt);
            mysqli_close($conn);
        }
    }

    function Order_add($key, $qty, $date)
    {
        // Include the configuration file
        $config = require 'config.php';

        // Create a new mysqli object with the configuration parameters
        $conn = new mysqli(
            $config['servername'],
            $config['username'],
            $config['password'],
            $config['dbname']
        );
        $status = "pending";

        if ($conn->connect_error) {
            $errorMsg = "Connection failed: " . $conn->connect_error;
            echo ($errorMsg);
        } else {
            //prepare order add 
            $stmt = mysqli_prepare($conn, "INSERT INTO mechkeys.order (customer_id, product_id, order_quantity, order_tracking_no, order_status) VALUES (?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "iiiss", $_SESSION['customer_id'], $key, $qty, $date, $status);
            // Execute the statement
            mysqli_stmt_execute($stmt);

            //close connection
            mysqli_stmt_close($stmt);
            mysqli_close($conn);
        }
    }

    function sendEmail($date)
    {
        $email = getCustomerEmail();
        $subject = "Order number:$date";
        $message = "Dear Valued customer, Thank you for purchasing from MechKeys! We sincerely hope you are satisfied with your order. Best regards, The MechKeys Team";

        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = $_ENV['PHPMAIL1_USERNAME'];
        $mail->Password = $_ENV['PHPMAIL1_PASSWORD'];
        $mail->Port = 465;
        $mail->SMTPSecure = 'ssl';
        $mail->isHTML(true);

        $mail->setFrom($email, 'MechKeys');
        $mail->addAddress($email); // Send to the Customer's email
        $mail->Subject = ("$subject");
        $mail->Body = $message;

        if ($mail->send()) {
            unset($_SESSION['cart']);
            display_errorMsg('Your order has been approved!');
            header("Location: ../profile.php");
            exit();
        } else {
            echo "Email sending failed: " . $mail->ErrorInfo;
        }

    }

    function getCustomerEmail()
    {
        // Include the configuration file
        $config = require 'config.php';

        // Create a new mysqli object with the configuration parameters
        $conn = new mysqli(
            $config['servername'],
            $config['username'],
            $config['password'],
            $config['dbname']
        );

        if ($conn->connect_error) {
            $errorMsg = "Connection failed: " . $conn->connect_error;
            echo ($errorMsg);
        } else {
            //prepare order add 
            $customer_id = $_SESSION['customer_id'];
            $stmt = mysqli_prepare($conn, "SELECT customer_email FROM customer WHERE customer_id = ?");
            $stmt->bind_param("s", $customer_id);

            // Execute the statement
            mysqli_stmt_execute($stmt);

            // Get the result set
            $result = mysqli_stmt_get_result($stmt);

            //get the product current quantity
            while ($row = mysqli_fetch_assoc($result)) {
                $customer_email = $row['customer_email'];
            }

            //close connection
            mysqli_stmt_close($stmt);
            mysqli_close($conn);
            return $customer_email;
        }
    }

    function sanitize_input($data)
    {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }

    CheckOutCart();


}