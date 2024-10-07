<?php
include '../../config.php';
$conn = new mysqli('localhost', 'u450897284_root', 'Lfisgemsdb1234', 'u450897284_lfis_db');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the request is a POST request and the ID is set
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id'])) {
    $message_id = intval($_POST['id']);
    
    // Check if the message exists before attempting to delete
    $check_sql = "SELECT id FROM message_history WHERE id = ?";
    $stmt_check = $conn->prepare($check_sql);
    $stmt_check->bind_param("i", $message_id);
    $stmt_check->execute();
    $stmt_check->store_result();

    if ($stmt_check->num_rows > 0) {
        // Proceed with the delete query
        $stmt_delete = $conn->prepare("DELETE FROM message_history WHERE id = ?");
        $stmt_delete->bind_param("i", $message_id);

        if ($stmt_delete->execute()) {
            // If delete was successful, return a JSON response
            echo json_encode(['success' => true]);
        } else {
            // If delete failed, return a JSON error message
            echo json_encode(['success' => false, 'error' => 'Failed to delete the message.']);
        }

        $stmt_delete->close();
    } else {
        // Return an error if the message does not exist
        echo json_encode(['success' => false, 'error' => 'Message not found.']);
    }

    $stmt_check->close();
} else {
    // If the request method is incorrect or ID is not set, return an error
    echo json_encode(['success' => false, 'error' => 'Invalid request.']);
}

$conn->close();
?>

