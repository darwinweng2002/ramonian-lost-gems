<?php
include '../../config.php'; // Include the database configuration

// Ensure the request is a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if the user ID is provided
    if (isset($_POST['id']) && is_numeric($_POST['id'])) {
        $userId = intval($_POST['id']);

        // Database connection
        $conn = new mysqli("localhost", "u450897284_root", "Lfisgemsdb1234", "u450897284_lfis_db");

        // Check connection
        if ($conn->connect_error) {
            die(json_encode(['success' => false, 'message' => "Connection failed: " . $conn->connect_error]));
        }

        // Delete user query
        $sql = "DELETE FROM user_staff WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $userId);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => "User deleted successfully."]);
        } else {
            echo json_encode(['success' => false, 'message' => "Error deleting user."]);
        }

        $stmt->close();
        $conn->close();
    } else {
        echo json_encode(['success' => false, 'message' => "Invalid user ID."]);
    }
} else {
    echo json_encode(['success' => false, 'message' => "Invalid request method."]);
}
