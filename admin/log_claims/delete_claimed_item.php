<?php
include '../../config.php'; // Adjust the path if necessary

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

    // Check if the item is from missing_items or message_history
    $sql = "UPDATE missing_items SET status = 'archived' WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $itemId);

    if ($stmt->execute() && $stmt->affected_rows > 0) {
        echo json_encode(['success' => true, 'message' => 'Item removed successfully.']);
    } else {
        // Try the other table if it wasn't found in the missing_items table
        $sql2 = "UPDATE message_history SET status = 'archived' WHERE id = ?";
        $stmt2 = $conn->prepare($sql2);
        $stmt2->bind_param('i', $itemId);

        if ($stmt2->execute() && $stmt2->affected_rows > 0) {
            echo json_encode(['success' => true, 'message' => 'Item removed successfully.']);
        } else {
            echo json_encode(['success' => false, 'error' => 'No rows were updated.']);
        }
    }

    $stmt->close();
    $conn->close();
    
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
}
?>
