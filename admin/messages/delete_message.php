<?php
include '../../config.php';
error_reporting(0);

$response = array('success' => false, 'error' => '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['id'])) {
        $messageId = intval($_POST['id']);
        $sql = "DELETE FROM message_history WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $messageId);
        
        if ($stmt->execute()) {
            $response['success'] = true;
        } else {
            $response['error'] = 'Failed to delete the message.';
        }
        $stmt->close();
    } else {
        $response['error'] = 'Invalid request.';
    }
} else {
    $response['error'] = 'Invalid request method.';
}

// Ensure nothing else is output before the JSON
header('Content-Type: application/json');
echo json_encode($response);

$conn->close();
