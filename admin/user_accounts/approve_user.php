<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../../config.php';

// Ensure this script only handles POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['user_id'])) {
        $userId = $conn->real_escape_string($_POST['user_id']);

        // Update the user's status to "active"
        $sql = "UPDATE user_staff SET status = 'active' WHERE id = '$userId'";

        if ($conn->query($sql) === TRUE) {
            echo '1';  // Success response
        } else {
            echo '0';  // Error response
            error_log('SQL Error: ' . $conn->error);  // Log error for debugging
        }
    } else {
        echo '0';  // Missing user_id parameter
        error_log('Missing user_id in POST request');
    }
} else {
    echo '0';  // Wrong request method
    error_log('Invalid request method');
}

$conn->close();
?>
