<?php
include '../../config.php';

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Check if the request is POST and the claim_id is set
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['claim_id'])) {
    $claimId = intval($_POST['claim_id']);
    
    if ($claimId > 0) {
        // Prepare and execute the delete query
        $stmt = $conn->prepare("DELETE FROM claimer WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param('i', $claimId);

            if ($stmt->execute()) {
                // If the delete is successful, redirect to the view claims page with a success message
                header('Location: view_claims.php?delete_success=1');
                exit();
            } else {
                // Log the error in deletion
                error_log("Error executing query: " . $stmt->error);
                header('Location: view_claims.php?delete_error=1');
                exit();
            }
            $stmt->close();
        } else {
            // Log the error if the statement fails
            error_log("Failed to prepare statement: " . $conn->error);
            header('Location: view_claims.php?delete_error=1');
            exit();
        }
    } else {
        // Log if the claim_id is invalid
        error_log("Invalid claim ID: " . $claimId);
        header('Location: view_claims.php?delete_error=1');
        exit();
    }
} else {
    // Log if the POST request or claim_id is missing
    error_log("Invalid request or missing claim_id");
    header('Location: view_claims.php?delete_error=1');
    exit();
}

$conn->close();
?>
