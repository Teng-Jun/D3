<?php
include "../sessions/sessiontimeout.php";

$fname = htmlspecialchars($_POST['fname']);
$email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
$adr = htmlspecialchars($_POST['adr']);
$city = htmlspecialchars($_POST['city']);
$state = htmlspecialchars($_POST['state']);
$zip = htmlspecialchars($_POST['zip']);

$cname = htmlspecialchars($_POST['cname']);
$cnumber = filter_input(INPUT_POST, 'cnumber', FILTER_SANITIZE_NUMBER_INT);
$cexpire = htmlspecialchars($_POST['cexpire']); // Note: Expiry date format should be further validated if needed
$cvv = filter_input(INPUT_POST, 'cvv', FILTER_SANITIZE_NUMBER_INT);

$success = true;
$errorMsg = "";

// full name check
if (empty($_POST["fname"])) {
    display_errorMsg("Full Name is required.");
    $success = false;
} else {
    $fname = sanitize_input($_POST["fname"]);
    if (!preg_match("/^[a-zA-Z ]*$/", $fname)) {
        display_errorMsg("Only alphabets and white space are allowed in the last name.");
        $success = false;
    }
}
//email check
if (empty($_POST["email"])) {
    display_errorMsg("Email is required.");
    $success = false;
} else {
    $email = sanitize_input($_POST["email"]);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        display_errorMsg("Invalid email format.");
        $success = false;
    }
}
//address check
if (empty($_POST["adr"])) {
    display_errorMsg("Address is required.");
    $success = false;
} else {
    $adr = sanitize_input($_POST["adr"]);
    if (!preg_match("/^[a-zA-Z0-9\s\.,#-]*$/", $adr)) {
        display_errorMsg("Only numbers, alphabets, spaces, commas, periods, hash, and dash are allowed for address");
        $success = false;
    }
}

// city check
if (empty($_POST["city"])) {
    display_errorMsg("City is required.");
    $success = false;
} else {
    $city = sanitize_input($_POST["city"]);
    if (!preg_match("/^[a-zA-Z\s]*$/", $city)) {
        display_errorMsg("Only letters and spaces are allowed for city.");
        $success = false;
    }
}

// state check
if (empty($_POST["state"])) {
    display_errorMsg("State is required.");
    $success = false;
} else {
    $state = sanitize_input($_POST["state"]);
    if (!preg_match("/^[a-zA-Z\s]*$/", $state)) {
        display_errorMsg("Only letters and spaces are allowed for state.");
        $success = false;
    }
}

// zip check
if (empty($_POST["zip"])) {
    display_errorMsg("Zip is required.");
    $success = false;
} else {
    $zip = sanitize_input($_POST["zip"]);
    if (!preg_match("/^[0-9]*$/", $zip)) {
        display_errorMsg("Only numbers are allowed for zip.");
        $success = false;
    }
}

// card number fill check
if (empty($_POST["cnumber"])) {
    display_errorMsg("Credit card number is required.");
    $success = false;
}

// Identify the type of card based on the first digit of the card number
$first_digit = substr($cnumber, 0, 1);
if ($first_digit == '4') {
    $card_type = 'Visa';
    $valid_card_length = 16;
    $valid_security_code_length = 3;
} elseif ($first_digit == '5') {
    $card_type = 'Mastercard';
    $valid_card_length = 16;
    $valid_security_code_length = 3;
} else {
    $card_type = 'Unknown';
    $valid_card_length = 0;
    $valid_security_code_length = 0;
    display_errorMsg("Unknown or Unaccepted Card type.");
    $success = false;
}

// Check if the card number is a valid length for the card type
if (strlen($cnumber) != $valid_card_length) {
    display_errorMsg("Invalid card number length.");
    $success = false;
}

// Check if the card number passes the Luhn algorithm
if (!luhn_algorithm($cnumber)) {
    display_errorMsg("Invalid card number.");
    $success = false;
}

// Check if the card name is well-formed
if (empty($_POST["cname"])) {
    display_errorMsg("Full Name on Credit Card is required.");
    $success = false;
} else {
    $cname = sanitize_input($_POST["cname"]);
    if (!preg_match("/^[a-zA-Z ]*$/", $cname)) {
        display_errorMsg("Only alphabets and white space are allowed in the Card Name.");
        $success = false;
    }
}

// card expire check
if (empty($_POST["cexpire"])) {
    display_errorMsg("Expiry date is required.");
    $success = false;
} else {
    $cexpire = sanitize_input($_POST["cexpire"]);
    if (!is_valid_expiry_date($cexpire)) {
        display_errorMsg("Invalid expiry date.");
        $success = false;
    }
}

// Check if the security code is the expected length for the card type
if (empty($_POST["cvv"])) {
    display_errorMsg("CVV is required.");
    $success = false;
} else {
    $cvv = sanitize_input($_POST["cvv"]);
    if (!valid_security_code_length($cvv)) {
        display_errorMsg("Invalid security code length or security code should only contain numbers.");
        $success = false;
    }
}

/**
 * Implementation of the Luhn algorithm for credit card validation.
 *
 * @param string $number The credit card number to validate.
 * @return bool Whether the credit card number is valid or not.
 */
function luhn_algorithm($cnumber)
{
    $sum = 0;
    $alt = false;
    for ($i = strlen($cnumber) - 1; $i >= 0; $i--) {
        $digit = intval($cnumber[$i]);
        if ($alt) {
            $digit *= 2;
            if ($digit > 9) {
                $digit -= 9;
            }
        }
        $sum += $digit;
        $alt = !$alt;
    }
    return $sum % 10 == 0;
}

/**
 * Checks if an expiry date string is a valid date in the future.
 *
 * @param string $date The expiry date string in the format MM/YY.
 * @return bool Whether the date is valid and in the future or not.
 */
function is_valid_expiry_date($date)
{
    $cexpire = DateTime::createFromFormat('m/y', $date);
    if (!$cexpire) {
        return false;
    }
    $current_date = new DateTime();
    return $cexpire > $current_date;
}

/**
 * Checks if the security code is the expected length for the card type.
 *
 * @param string $cvv The security code to validate.
 * @return bool Whether the security code is valid or not.
 */
function valid_security_code_length($cvv)
{
    global $valid_security_code_length;
    return preg_match('/^[0-9]+$/', $cvv) && strlen($cvv) == $valid_security_code_length;
}

// sanitize user input
function sanitize_input($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/**
 * Function to display error messages.
 *
 * @param string $message The error message to display.
 */
// echo $success;
// if (isset($_SESSION['errorMsg'])) {
//     echo "<div class='errorMsg'>"; 
//     foreach ($_SESSION['errorMsg'] as $message) {
//         echo "<p class='error'>" . htmlspecialchars($message) . "</p>";
//     }
//     echo "</div>";
//     unset($_SESSION['errorMsg']); // Clear the error message after displaying it
// }


if (!isset($_SESSION['errorMsg'])) {

    $_SESSION['checkout_step'] = "finish";
    header("Location: checkoutcart_process.php");
    exit();

} else {
    $_SESSION['checkout_step'] = 'checkout';
    header("Location: ../checkout.php");
    exit();

}
?>