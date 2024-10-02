<?php
// Include database connection
include '../../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve the user ID
    $user_id = $_POST['user_id'];

    // Ensure you have a valid user ID
    if (!empty($user_id)) {
        // Prepare and execute update query to approve the user
        $stmt = $conn->prepare("UPDATE user_staff SET status = 'active' WHERE id = ?");
        $stmt->bind_param("i", $user_id);

        if ($stmt->execute()) {
            // Return success response (1)
            echo "1";
        } else {
            // If something went wrong with the query, return error response
            echo "0";
        }

        $stmt->close();
    } else {
        // Missing user_id
        echo "0";
    }

    $conn->close();
}
