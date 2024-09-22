<?php
include '../../config.php'; // Adjust the path as necessary

header('Content-Type: application/json'); // Ensure the response is JSON

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    // Database connection
    $conn = new mysqli('localhost', 'u450897284_root', 'Lfisgemsdb1234', 'u450897284_lfis_db');
    
    if ($conn->connect_error) {
        echo json_encode(['success' => false, 'error' => 'Connection failed: ' . $conn->connect_error]);
        exit();
    }

    // Get the claim ID (from the claims table)
    $claimId = intval($_POST['id']);

    if ($claimId <= 0) {
        echo json_encode(['success' => false, 'error' => 'Invalid claim ID']);
        exit();
    }

    // Update the status in the claims table to 'archived' (or any other appropriate value)
    $stmt = $conn->prepare("UPDATE claims SET status = 'archived' WHERE id = ?");
    
    if (!$stmt) {
        echo json_encode(['success' => false, 'error' => 'Failed to prepare statement: ' . $conn->error]);
        exit();
    }

    $stmt->bind_param('i', $claimId);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode(['success' => true, 'message' => 'Claim archived successfully.']);
        } else {
            echo json_encode(['success' => false, 'error' => 'No rows were updated.']);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to execute the update: ' . $stmt->error]);
    }

    $stmt->close();
    $conn->close();
    
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
}
?>
