<?php
include '../../config.php'; // Adjust path if necessary

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    // Database connection
    $conn = new mysqli('localhost', 'u450897284_root', 'Lfisgemsdb1234', 'u450897284_lfis_db');
    
    if ($conn->connect_error) {
        die(json_encode(['success' => false, 'error' => 'Connection failed: ' . $conn->connect_error]));
    }
    
    $itemId = intval($_POST['id']);
    
    // Instead of deleting, update the status to mark it as removed from the Claim History
    $newStatus = 3; // Assuming 3 is "removed" or "archived"
    $stmt = $conn->prepare("UPDATE missing_items SET status = ? WHERE id = ?");
    $stmt->bind_param('ii', $newStatus, $itemId);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => $stmt->error]);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
}
?>
