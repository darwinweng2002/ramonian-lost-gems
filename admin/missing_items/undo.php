<?php
include '../../config.php';

// Get the item ID from the POST request
$item_id = isset($_POST['id']) ? intval($_POST['id']) : 0;

$response = ['success' => false, 'error' => ''];

if ($item_id > 0) {
    // Update the is_denied column back to 0 (undo deny)
    $stmt = $conn->prepare("UPDATE missing_items SET is_denied = 0 WHERE id = ?");
    $stmt->bind_param("i", $item_id);
    if ($stmt->execute()) {
        $response['success'] = true;
    } else {
        $response['error'] = "Failed to update the item status.";
    }
    $stmt->close();
} else {
    $response['error'] = "Invalid item ID.";
}

// Return JSON response
echo json_encode($response);
?>
