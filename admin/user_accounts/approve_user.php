<?php
include '../../config.php'; // Include your DB configuration

// Check if the request method is POST and user_id is set
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'])) {
    $user_id = intval($_POST['user_id']);

    // Prepare the SQL query to update the status to 'active'
    $sql = "UPDATE user_staff SET status = 'active' WHERE id = ?";
    
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param('i', $user_id); // Bind the user_id to the query

        if ($stmt->execute()) {
            echo '1'; // Return success message
        } else {
            echo '0'; // Return failure message
        }

        $stmt->close();
    } else {
        echo '0'; // Return failure if SQL preparation fails
    }
} else {
    echo '0'; // Invalid request
}

$conn->close();
?>
