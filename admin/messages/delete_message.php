<?php
include '../../config.php';

// Check if ID is set
if (isset($_POST['id'])) {
    $user_id = $_POST['id'];

    // Start by deleting any activities the user performed in the system
    // Example: Deleting user's posts from found_items table
    $deletePostsSql = "DELETE FROM message_history WHERE user_id = ?";
    $stmt = $conn->prepare($deletePostsSql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    // Now, delete the user from the user_member table
    $deleteUserSql = "DELETE FROM user_member WHERE id = ?";
    $stmt = $conn->prepare($deleteUserSql);
    $stmt->bind_param("i", $user_id);
    if ($stmt->execute()) {
        // Send success response
        echo '1';
    } else {
        // Send error response
        echo '0';
    }

    $stmt->close();
    $conn->close();
} else {
    // No ID provided
    echo '0';
}
?>
