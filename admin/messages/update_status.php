<?php
include '../../config.php';

// Database connection
$conn = new mysqli('localhost', 'u450897284_root', 'Lfisgemsdb1234', 'u450897284_lfis_db');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the posted data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $itemId = intval($_POST['id']);
    $newStatus = intval($_POST['status']);
    
    // Check that the status is valid (between 0 and 3)
    if ($newStatus >= 0 && $newStatus <= 4) {
        // Update the status in the database
        $stmt = $conn->prepare("UPDATE message_history SET status = ? WHERE id = ?");
        $stmt->bind_param('ii', $newStatus, $itemId);
        
        if ($stmt->execute()) {
            // If the item is published, also update the category status
            if ($newStatus == 1) { // 1 = Published
                // Set category status to 'published' when the item is published
                $stmtCategory = $conn->prepare("UPDATE categories SET status = 1 
                                                WHERE id = (SELECT category_id FROM message_history WHERE id = ? AND user_id IS NOT NULL)");
                $stmtCategory->bind_param("i", $itemId);
                $stmtCategory->execute();
                $stmtCategory->close();
            }

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
