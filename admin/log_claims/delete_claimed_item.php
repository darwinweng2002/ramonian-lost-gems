<?php
include '../../config.php'; // Adjust the path as necessary

header('Content-Type: application/json'); // Ensure the response is JSON

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    // Database connection
    $conn = new mysqli('localhost', 'u450897284_root', 'Lfisgemsdb1234', 'u450897284_lfis_db');
    
    if ($conn->connect_error) {
        echo json_encode(['success' => false, 'error' => 'Connection failed: ' . $conn->connect_error]);
        exit();
    }

    // Get the item ID
    $itemId = intval($_POST['id']);

    if ($itemId <= 0) {
        echo json_encode(['success' => false, 'error' => 'Invalid item ID']);
        exit();
    }

    // Update the status in the claim_history table to 'archived'
    $stmt = $conn->prepare("UPDATE claim_history SET status = 'archived' WHERE item_id = ?");
    
    if (!$stmt) {
        echo json_encode(['success' => false, 'error' => 'Failed to prepare statement: ' . $conn->error]);
        exit();
    }

    $stmt->bind_param('i', $itemId);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Item archived successfully.']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to update the status: ' . $stmt->error]);
    }

    $stmt->close();
    $conn->close();
    
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
}
?>
