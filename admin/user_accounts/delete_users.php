<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../../config.php';

// Ensure this script only handles POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['id'])) {
        $userId = $conn->real_escape_string($_POST['id']);

        // Delete the user from the database
        $sql = "DELETE FROM user_staff WHERE id = '$userId'";

        if ($conn->query($sql) === TRUE) {
            echo '1';  // Success response
        } else {
            echo '0';  // Error response
            error_log('SQL Error: ' . $conn->error);  // Log error for debugging
        }
    } else {
        echo '0';  // Missing id parameter
        error_log('Missing id in POST request');
    }
} else {
    echo '0';  // Wrong request method
    error_log('Invalid request method');
}

$conn->close();
?>
