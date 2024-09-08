<?php
include '../../config.php';

header('Content-Type: application/json');

$response = ['success' => false, 'error' => 'Unknown error occurred'];

// Check if the ID is provided
if (isset($_POST['id'])) {
    $messageId = intval($_POST['id']);

    // Database connection
    $conn = new mysqli('localhost', 'root', '1234', 'lfis_db'); // Replace with your actual DB connection details

    if ($conn->connect_error) {
        $response['error'] = "Connection failed: " . $conn->connect_error;
    } else {
        // Example SQL to "publish" a message
        $sql = "UPDATE message_history SET published = 1 WHERE id = ?";
        $stmt = $conn->prepare($sql);

        if ($stmt) {
            $stmt->bind_param('i', $messageId);

            if ($stmt->execute()) {
                $response['success'] = true;
                unset($response['error']); // Clear the error message on success
            } else {
                $response['error'] = "Failed to execute query: " . $stmt->error;
            }

            $stmt->close();
        } else {
            $response['error'] = "Failed to prepare statement: " . $conn->error;
        }

        $conn->close();
    }
} else {
    $response['error'] = 'No message ID provided';
}

echo json_encode($response);
?>
