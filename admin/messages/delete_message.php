<?php
// Include your database connection
include '../../config.php';

// Check if ID is received in POST request
if (isset($_POST['id'])) {
    $message_id = intval($_POST['id']); // Sanitize input

    // Prepare SQL query to delete the message by ID
    $sql = "DELETE FROM message_history WHERE id = ?";
    
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $message_id);
        
        // Execute the query
        if ($stmt->execute()) {
            // Success, return JSON response
            echo json_encode(['success' => true]);
        } else {
            // Error during execution
            echo json_encode(['success' => false, 'error' => 'Database error.']);
        }
        
        $stmt->close();
    } else {
        // Error preparing the statement
        echo json_encode(['success' => false, 'error' => 'Failed to prepare SQL.']);
    }
} else {
    // No ID provided
    echo json_encode(['success' => false, 'error' => 'No ID provided.']);
}

$conn->close();
?>
