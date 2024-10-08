<?php
include '../../config.php';
$conn = new mysqli('localhost','u450897284_root', 'Lfisgemsdb1234', 'u450897284_lfis_db'); // Replace with your actual DB connection details

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
// Check if the request is a POST request and the ID is set
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id'])) {
    $message_id = intval($_POST['id']);

    // Prepare and execute the delete query
    $stmt = $conn->prepare("DELETE FROM message_history WHERE id = ?");
    $stmt->bind_param("i", $message_id);

    if ($stmt->execute()) {
        // If delete was successful, return a JSON response
        echo json_encode(['success' => true]);
    } else {
        // If delete failed, return a JSON error message
        echo json_encode(['success' => false, 'error' => 'Failed to delete the message']);
    }

    $stmt->close();
} else {
    // If the request method is incorrect or ID is not set, return an error
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
}

$conn->close();