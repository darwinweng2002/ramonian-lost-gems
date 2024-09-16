<?php
include '../../config.php';

// Database connection
$conn = new mysqli('localhost', 'u450897284_root', 'Lfisgemsdb1234', 'u450897284_lfis_db');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the POST data
$message_id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$new_status = isset($_POST['status']) ? $_POST['status'] : '';

if ($message_id > 0 && in_array($new_status, ['published', 'claimed', 'surrendered', 'pending'])) {
    // Prepare and bind statement
    $stmt = $conn->prepare("UPDATE message_history SET status = ? WHERE id = ?");
    $stmt->bind_param('si', $new_status, $message_id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => $stmt->error]);
    }

    $stmt->close();
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request.']);
}

$conn->close();
?>
