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
        // If the item is published, update the category status
        if ($status == 1) { // 1 = Published
            // Set the related category status to 'published' when the missing item is published
            $stmtCategory = $conn->prepare("UPDATE categories SET status = 1 
                                            WHERE id = (SELECT category_id FROM missing_items WHERE id = ? AND user_id IS NOT NULL)");
            $stmtCategory->bind_param("i", $itemId);
            $stmtCategory->execute();
            $stmtCategory->close();
        }

        // If the item is not published (e.g., claimed, pending, surrendered), mark the category as not published
        if ($status == 0 || $status == 2 || $status == 3 || $status == 4) { // Pending, Claimed, or Surrendered
            $stmtCategory = $conn->prepare("UPDATE categories SET status = 0 
                                            WHERE id = (SELECT category_id FROM missing_items WHERE id = ? AND user_id IS NOT NULL)");
            $stmtCategory->bind_param("i", $itemId);
            $stmtCategory->execute();
            $stmtCategory->close();
        }

        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => $stmt->error]);
    }

    $stmt->close();
}

$conn->close();
?>
