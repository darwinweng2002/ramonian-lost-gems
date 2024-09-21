<?php
include '../../config.php';

// Database connection
$conn = new mysqli('localhost', 'u450897284_root', 'Lfisgemsdb1234', 'u450897284_lfis_db');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_POST['id']) && isset($_POST['status'])) {
    $itemId = intval($_POST['id']);
    $status = intval($_POST['status']);

    // Update status in the database
    $stmt = $conn->prepare("UPDATE missing_items SET status = ? WHERE id = ?");
    $stmt->bind_param('ii', $status, $itemId);
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => $stmt->error]);
    }

    $stmt->close();
}

$conn->close();
?>
