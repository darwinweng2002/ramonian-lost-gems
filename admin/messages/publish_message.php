<?php
include '../../config.php';

// Database connection
$conn = new mysqli('localhost', 'root', '1234', 'lost_db'); // Replace with your actual DB connection details

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the ID is set in the POST request
if (isset($_POST['id'])) {
    $messageId = intval($_POST['id']);

    // Update the message's is_published status to 1
    $sql = "UPDATE message_history SET is_published = 1 WHERE id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $messageId);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => $stmt->error]);
    }

    $stmt->close();
} else {
    echo json_encode(['success' => false, 'error' => 'No ID provided']);
}

$conn->close();
?>
