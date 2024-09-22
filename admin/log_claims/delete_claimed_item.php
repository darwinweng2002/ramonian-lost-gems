<?php
include '../../config.php'; // Adjust path if necessary

header('Content-Type: application/json'); // Ensure JSON response

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    
    // Database connection
    $conn = new mysqli('localhost', 'u450897284_root', 'Lfisgemsdb1234', 'u450897284_lfis_db');
    
    if ($conn->connect_error) {
        echo json_encode(['success' => false, 'error' => 'Connection failed: ' . $conn->connect_error]);
        exit();
    }

    // Get the item ID and user ID (assuming the user is logged in and their ID is stored in session)
    $itemId = intval($_POST['id']);
    $userId = intval($_SESSION['user_id']); // Assuming you have user sessions

    if ($itemId <= 0 || $userId <= 0) {
        echo json_encode(['success' => false, 'error' => 'Invalid item or user ID']);
        exit();
    }

    // Insert into claim_history
    $stmt = $conn->prepare("INSERT INTO claim_history (item_id, user_id, status) VALUES (?, ?, 'claimed')");
    
    if (!$stmt) {
        echo json_encode(['success' => false, 'error' => 'Failed to prepare statement: ' . $conn->error]);
        exit();
    }

    $stmt->bind_param('ii', $itemId, $userId);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to execute statement: ' . $stmt->error]);
    }

    $stmt->close();
    $conn->close();
    
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
}
?>
