<?php
include '../../config.php';

$conn = new mysqli('localhost', 'u450897284_root', 'Lfisgemsdb1234', 'u450897284_lfis_db');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if it's a POST request to update the status
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $itemId = intval($_POST['id']);
    $newStatus = intval($_POST['status']);  // Convert status to integer for proper comparison

    // Debug: Check the values being posted
    error_log("Updating status for itemId $itemId with newStatus $newStatus");

    // Check if the status is within the valid range
    if (in_array($newStatus, [0, 1, 2, 3, 4])) {
        // Update the status in the database
        $stmt = $conn->prepare("UPDATE message_history SET status = ? WHERE id = ?");
        $stmt->bind_param('ii', $newStatus, $itemId);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => $stmt->error]);
        }
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid status value']);
    }
}

$conn->close();
?>
