<?php
include '../../config.php'; // Adjust path if necessary

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    // Database connection
    $conn = new mysqli('localhost', 'u450897284_root', 'Lfisgemsdb1234', 'u450897284_lfis_db');
    
    if ($conn->connect_error) {
        die(json_encode(['success' => false, 'error' => 'Connection failed: ' . $conn->connect_error]));
    }
    
    $itemId = intval($_POST['id']);
    
    // Prepare and execute delete statement
    $stmt = $conn->prepare("DELETE FROM missing_items WHERE id = ?");
    $stmt->bind_param('i', $itemId);

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
