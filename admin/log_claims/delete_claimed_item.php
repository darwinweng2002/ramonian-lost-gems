<?php
include '../../config.php'; // Adjust path if necessary

header('Content-Type: application/json'); // Ensure JSON response

// Check if the request method is POST and if the 'id' is set
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    
    // Database connection
    $conn = new mysqli('localhost', 'u450897284_root', 'Lfisgemsdb1234', 'u450897284_lfis_db');
    
    if ($conn->connect_error) {
        // Send JSON response for connection error
        echo json_encode(['success' => false, 'error' => 'Connection failed: ' . $conn->connect_error]);
        exit();
    }

    // Get the item ID from the POST request and sanitize it
    $itemId = intval($_POST['id']);

    // Check if itemId is valid (not zero)
    if ($itemId <= 0) {
        echo json_encode(['success' => false, 'error' => 'Invalid item ID']);
        exit();
    }

    // Prepare SQL statement to update status
    $newStatus = 3; // Assuming 3 means "archived"
    $stmt = $conn->prepare("UPDATE missing_items SET status = ? WHERE id = ?");
    
    if (!$stmt) {
        // Check if the SQL statement preparation failed
        echo json_encode(['success' => false, 'error' => 'Failed to prepare statement: ' . $conn->error]);
        exit();
    }

    // Bind the parameters to the statement
    $stmt->bind_param('ii', $newStatus, $itemId);

    // Execute the statement and check for errors
    if ($stmt->execute()) {
        // Success
        echo json_encode(['success' => true]);
    } else {
        // Error during execution
        echo json_encode(['success' => false, 'error' => 'Failed to execute statement: ' . $stmt->error]);
    }

    // Close the statement and connection
    $stmt->close();
    $conn->close();
    
} else {
    // Invalid request
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
}
?>
