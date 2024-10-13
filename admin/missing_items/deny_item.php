<?php
include '../../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $itemId = isset($_POST['id']) ? intval($_POST['id']) : 0;

    if ($itemId > 0) {
        // Deny the item by setting is_denied = 1 in the missing_items table
        $sql = "UPDATE missing_items SET is_denied = 1 WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $itemId);

        if ($stmt->execute()) {
            // Successfully denied the item
            echo json_encode(['success' => true]);
        } else {
            // Failed to update the item
            echo json_encode(['success' => false, 'error' => 'Failed to deny the item.']);
        }

        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid item ID.']);
    }
}

$conn->close();
?>
