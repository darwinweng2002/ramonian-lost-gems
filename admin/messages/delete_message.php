<?php
include '../../config.php';

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

$conn = new mysqli('localhost', 'root', '1234', 'lfis_db');

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'error' => 'Database connection failed']);
    exit();
}

$messageId = isset($_POST['id']) ? intval($_POST['id']) : 0;

if ($messageId > 0) {
    $conn->begin_transaction();

    try {
        // First, fetch the images related to the message
        $stmt = $conn->prepare("SELECT image_path FROM message_images WHERE message_id = ?");
        $stmt->bind_param('i', $messageId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $imagePaths = [];
        while ($row = $result->fetch_assoc()) {
            $imagePaths[] = $row['image_path'];
        }
        $stmt->close();
        
        // Delete the images from the server
        foreach ($imagePaths as $imagePath) {
            // Construct the full path to the image file
            $fullImagePath = '/path/to/uploads/items/' . $imagePath; // Update this path based on your server configuration
            
            if (file_exists($fullImagePath)) {
                unlink($fullImagePath); // Delete the file from the server
            }
        }
        
        // Delete associated images from the database
        $stmt = $conn->prepare("DELETE FROM message_images WHERE message_id = ?");
        $stmt->bind_param('i', $messageId);
        $stmt->execute();
        $stmt->close();
        
        // Delete the message history
        $stmt = $conn->prepare("DELETE FROM message_history WHERE id = ?");
        $stmt->bind_param('i', $messageId);
        $stmt->execute();
        $stmt->close();
        
        $conn->commit();
        
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'error' => 'Failed to delete the message']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid message ID']);
}

$conn->close();
?>
