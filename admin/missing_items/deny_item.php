<?php
include '../../config.php';

// Get the missing item ID from the POST request
$missing_item_id = isset($_POST['id']) ? intval($_POST['id']) : 0;

$response = ['success' => false, 'error' => ''];

if ($missing_item_id > 0) {
    // Update the is_denied column to 1 (denied) for the missing item
    $stmt = $conn->prepare("UPDATE missing_items SET is_denied = 1 WHERE id = ?");
    $stmt->bind_param("i", $missing_item_id);
    if ($stmt->execute()) {
        $response['success'] = true;
    } else {
        $response['error'] = "Failed to update the item status.";
    }
    $stmt->close();
} else {
    $response['error'] = "Invalid missing item ID.";
}

// Return JSON response
echo json_encode($response);
?>
