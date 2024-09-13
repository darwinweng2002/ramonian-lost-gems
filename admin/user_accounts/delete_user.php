<?php
include '../../config.php';

// Database connection
$conn = new mysqli("localhost", "u450897284_root", "u450897284_lfis_db", "Lfisgemsdb1234");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize ID
$id = isset($_POST['id']) ? intval($_POST['id']) : 0;

if ($id > 0) {
    // Begin transaction
    $conn->begin_transaction();

    try {
        // Delete related records from claim_requests
        $stmt = $conn->prepare("DELETE FROM claim_requests WHERE user_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();

        // Delete user record
        $stmt = $conn->prepare("DELETE FROM user_member WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            // Commit transaction
            $conn->commit();
            echo '1'; // Success
        } else {
            throw new Exception("User not found or already deleted.");
        }

        $stmt->close();
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        echo '0'; // Failure
    }

    $conn->close();
} else {
    echo '0'; // Invalid ID
}
?>
