<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['user_id'])) {
        $userId = $conn->real_escape_string($_POST['user_id']);

        // Update user's status to 'active'
        $sql = "UPDATE user_staff SET status = 'active' WHERE id = '$userId'";

        if ($conn->query($sql) === TRUE) {
            echo json_encode(['success' => true, 'message' => 'User approved successfully!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to approve user. SQL Error: ' . $conn->error]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid request. Missing user ID.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}

$conn->close();
?>
