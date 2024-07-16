<?php
// Start session
include "../sessions/sessiontimeout.php";
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['role'] !== 'admin') {
    header("Location: ../../index.php");
    exit;
}

// Current admin's ID from session
$admin_id = $_SESSION['admin_id'];
// Admin ID to be approved from GET request
$admin_id_to_approve = $_GET['admin_id'];

// Include the config file
$config = include ('config.php');

require_once '../../vendor/autoload.php';


// Create database connection
$conn = new mysqli($config['servername'], $config['username'], $config['password'], $config['dbname']);

function display_errorMsg($message)
{
    if (!isset($_SESSION['errorMsg'])) {
        $_SESSION['errorMsg'] = [];
    }
    $_SESSION['errorMsg'][] = $message;
}



// Check connection
if ($conn->connect_error) {
    display_errorMsg("Unable to connect to the service, please try again later.");
    header("Location: ../adminlist.php");
    exit();
}

// Check for existing admin record
if (empty($_SESSION['errorMsg'])) {
    // Prevent self-approval
    if ($admin_id == $admin_id_to_approve) {
        display_errorMsg("You cannot approve yourself.");
        header("Location: ../adminlist.php");
        exit();
    }

    $stmt = $conn->prepare("SELECT * FROM mechkeys.admin WHERE admin_id = ?");
    $stmt->bind_param("s", $admin_id_to_approve);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        if ($row = $result->fetch_assoc()) {
            if ($row['approved'] == 1) {
                display_errorMsg("Invalid ID");
                header("Location: ../adminlist.php");
                $stmt->close();
                exit();
            } elseif ($row['approved'] == 0) {
                // Get the current value of approved_by
                $approved_by = $row['approved_by'] ?? '';

                if (!empty($approved_by)) {
                    // Split the string by commas to get the items
                    $approved_by_items = explode(',', $approved_by);

                    // Check if the current admin_id is already in approved_by
                    if (in_array($admin_id, $approved_by_items)) {
                        display_errorMsg("You have already approved this user.");
                        header("Location: ../adminlist.php");
                        exit();
                    }

                    // Check how many items there are
                    if (count($approved_by_items) >= 2) {
                        // Exit the program if there are already 2 items
                        display_errorMsg("Already Approved");
                        header("Location: ../adminlist.php");
                        exit();
                    }

                    // If there are less than 2 items, concatenate the new admin_id
                    $approved_by .= ',' . $admin_id;
                } else {
                    // If approved_by is empty, assign admin_id directly
                    $approved_by_items = [];
                    $approved_by = $admin_id;
                }

                // Determine if approval threshold has been reached
                $approved = (count($approved_by_items) + 1) >= 2 ? 1 : 0;

                // Update the database with the new values of approved_by and approved status
                if ($stmt = $conn->prepare("UPDATE mechkeys.admin SET approved_by = ?, approved = ? WHERE admin_id = ?")) {
                    $stmt->bind_param("sii", $approved_by, $approved, $admin_id_to_approve);
                    $stmt->execute();

                    if ($stmt->affected_rows > 0) {
                        // Successful update
                        display_errorMsg('Admin details updated successfully.');
                        header("Location: ../adminlist.php");
                        $stmt->close();
                        exit();
                    } else {
                        // No records updated or something went wrong
                        display_errorMsg('Something went wrong, please try again later.');
                        header("Location: ../adminlist.php");
                        $stmt->close();
                        exit();
                    }
                    $stmt->close();
                }
            }
        }
    } else {
        display_errorMsg("Please try again boss.");
        header("Location: ../adminlist.php");
        exit();
    }
    $stmt->close();
}

// If there are errors, redirect back to admin list
if (!empty($_SESSION['errorMsg'])) {
    header("Location: ../adminlist.php");
    exit();
}

// Close the connection
$conn->close();
?>