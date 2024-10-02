<?php
include '../../config.php';

// Fetch the user ID from POST
$user_id = $_POST['id'] ?? null;

if ($user_id) {
    // Prepare the DELETE statement
    $stmt = $conn->prepare("DELETE FROM user_staff WHERE id = ?");
    $stmt->bind_param("i", $user_id);

    // Execute and check success
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'User deleted successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'An error occurred while deleting the user.']);
    }
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'No user ID provided.']);
}
$conn->close();
?>
