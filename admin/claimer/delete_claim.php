<?php
include '../../config.php';
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['claim_id'])) {
    $claimId = intval($_POST['claim_id']);

    // Check if claim ID is valid
    if ($claimId > 0) {
        $sql = "DELETE FROM claimer WHERE id = ?";
        $stmt = $conn->prepare($sql);

        if ($stmt) {
            $stmt->bind_param('i', $claimId);
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Claim deleted successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to delete claim. Error: ' . $stmt->error]);
            }
            $stmt->close();
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to prepare statement. Error: ' . $conn->error]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid claim ID.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request or claim ID not set.']);
}

$conn->close();
