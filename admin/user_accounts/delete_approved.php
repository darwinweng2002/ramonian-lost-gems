<?php
// Include the database configuration file
include '../../config.php';

// Check if the ID of the user to delete is passed in the request
if (isset($_POST['id']) && !empty($_POST['id'])) {
    $user_id = intval($_POST['id']); // Sanitize the ID

    // SQL statement to delete the user by ID
    $sql = "DELETE FROM user_member WHERE id = ?";

    // Prepare the statement
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $user_id);

        // Execute the query
        if ($stmt->execute()) {
            // Success: return a success response
            echo json_encode(['success' => true]);
        } else {
            // Failure: return an error response
            echo json_encode(['success' => false, 'message' => 'Failed to delete user']);
        }

        $stmt->close();
    } else {
        // SQL error
        echo json_encode(['success' => false, 'message' => 'SQL preparation error']);
    }
} else {
    // Invalid request
    echo json_encode(['success' => false, 'message' => 'Invalid user ID']);
}

$conn->close();
?>
