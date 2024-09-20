<?php
include '../../config.php';

// Database connection
$conn = new mysqli('localhost', 'u450897284_root', 'Lfisgemsdb1234', 'u450897284_lfis_db'); // Replace with your actual DB connection details

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the posted data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $itemId = intval($_POST['id']);
    $newStatus = intval($_POST['status']);
    
    // Ensure you are updating only the status, not deleting the record
    $stmt = $conn->prepare("UPDATE missing_items SET status = ? WHERE id = ?");
    $stmt->bind_param('ii', $newStatus, $itemId);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => $conn->error]);
    }
    $stmt->close();
}

$conn->close();
?>
