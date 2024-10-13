<?php
include '../../config.php';

// Get the message ID from the POST request
$message_id = isset($_POST['id']) ? intval($_POST['id']) : 0;

$response = ['success' => false, 'error' => ''];

if ($message_id > 0) {
    // Update the is_denied column to 1 (denied)
    $stmt = $conn->prepare("UPDATE message_history SET is_denied = 1 WHERE id = ?");
    $stmt->bind_param("i", $message_id);
    if ($stmt->execute()) {
        $response['success'] = true;
    } else {
        $response['error'] = "Failed to update the item status.";
    }
    $stmt->close();
} else {
    $response['error'] = "Invalid message ID.";
}

// Return JSON response
echo json_encode($response);
?>
