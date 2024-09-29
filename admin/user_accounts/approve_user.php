<?php
include '../../config.php';

// Check if the user_id is provided via POST
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['user_id'])) {
    $userId = intval($_POST['user_id']); // Sanitize the user ID

    // Database connection
    $conn = new mysqli("localhost", "u450897284_root", "Lfisgemsdb1234", "u450897284_lfis_db");

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Update the status of the user to 'active'
    $sql = "UPDATE user_staff SET status = 'active' WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $userId); // Bind the user ID

    if ($stmt->execute()) {
        echo '1'; // Return success
    } else {
        echo '0'; // Return failure
    }

    $stmt->close();
    $conn->close();
} else {
    echo 'Invalid request';
}
?>
