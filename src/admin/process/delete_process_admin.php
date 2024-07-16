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
$admin_id_to_delete = $_GET['admin_id'];

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
    if ($admin_id == $admin_id_to_delete) {
        display_errorMsg("You cannot approve yourself.");
        header("Location: ../adminlist.php");
        exit();
    }

    $stmt = $conn->prepare("SELECT * FROM mechkeys.admin WHERE admin_id = ?");
    $stmt->bind_param("i", $admin_id_to_delete);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        if ($row = $result->fetch_assoc()) {
            // Get the current value of approved_by
            $deleted_by = $row['deleted_by'] ?? '';

            if (!empty($deleted_by)) {
                // Split the string by commas to get the items
                $deleted_by_items = explode(',', $deleted_by);

                // Check if the current admin_id is already in approved_by
                if (in_array($admin_id, $deleted_by_items)) {
                    display_errorMsg("You have already voted to remove this user.");
                    header("Location: ../adminlist.php");
                    exit();
                }

                // Check how many items there are
                if (count($deleted_by_items) >= 2) {
                    // Exit the program if there are already 2 items
                    display_errorMsg("Already deleted");
                    header("Location: ../adminlist.php");
                    exit();
                }

                // If there are less than 2 items, concatenate the new admin_id
                $deleted_by .= ',' . $admin_id;
                if ($stmt = $conn->prepare("DELETE FROM mechkeys.admin WHERE admin_id = ?")) {
                    $stmt->bind_param("i", $admin_id_to_delete);
                    $stmt->execute();
                }
                if ($stmt->affected_rows > 0) {
                     "hello";
                    // Record deleted successfully
                    display_errorMsg('Admin deleted successfully.');
                    header("Location: ../adminlist.php");
                    $stmt->close();
                    exit();
                } else {
                    // No records deleted or something went wrong
                     "hello";
                    display_errorMsg('Something went wrong, please try again later.');
                    header("Location: ../adminlist.php");
                    $stmt->close();
                    exit();
                }

            } else {
                // If approved_by is empty, assign admin_id directly
                $deleted_by_items = [];
                $deleted_by = $admin_id;
            }

            // Determine if approval threshold has been reached
            $deleted = (count($deleted_by_items) + 1) >= 2 ? 1 : 0;

            // Update the database with the new values of approved_by and approved status
            if ($stmt = $conn->prepare("UPDATE mechkeys.admin SET deleted_by = ? WHERE admin_id = ?")) {
                $stmt->bind_param("si", $deleted_by, $admin_id_to_delete);
                $stmt->execute();

                if ($stmt->affected_rows > 0) {
                    // Successful update
                    display_errorMsg('Admin deleted successfully.');
                    header("Location: ../adminlist.php");
                    $stmt->close();
                    exit();
                } else {
                    // No records updated or something went wrong
                    display_errorMsg('Something went wrong, pldease try again later.');
                    header("Location: ../adminlist.php");
                    $stmt->close();
                    exit();
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