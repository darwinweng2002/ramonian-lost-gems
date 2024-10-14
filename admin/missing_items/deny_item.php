<?php
include '../../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $itemId = isset($_POST['id']) ? intval($_POST['id']) : 0;

    if ($itemId > 0) {
        // Mark the missing item as denied by updating the "is_denied" column
        $sql = "UPDATE missing_items SET is_denied = 1 WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $itemId);

        if ($stmt->execute()) {
            // Return a success response
            echo json_encode(['success' => true]);
        } else {
            // Return an error response if the query fails
            echo json_encode(['success' => false, 'error' => 'Failed to deny the item.']);
        }

        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid item ID.']);
    }
}

$conn->close();
?>
