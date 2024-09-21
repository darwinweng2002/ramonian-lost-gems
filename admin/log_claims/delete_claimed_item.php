<?php
include '../../config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id'])) {
    // Database connection
    $conn = new mysqli('localhost', 'u450897284_root', 'Lfisgemsdb1234', 'u450897284_lfis_db');

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $itemId = intval($_POST['id']);

    // Delete item from the database
    $stmt = $conn->prepare("DELETE FROM missing_items WHERE id = ?");
    $stmt->bind_param('i', $itemId);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to delete item.']);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request.']);
}
?>
