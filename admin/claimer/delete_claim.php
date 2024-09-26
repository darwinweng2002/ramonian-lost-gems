<?php
include '../../config.php'; // Include database configuration

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Check if the request method is POST and claim_id is set
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['claim_id'])) {
    // Get the claim_id from the POST request and sanitize it
    $claimId = intval($_POST['claim_id']);

    if ($claimId > 0) {
        // Prepare the delete statement
        $sql = "DELETE FROM claimer WHERE id = ?";
        $stmt = $conn->prepare($sql);

        if ($stmt) {
            // Bind the claim_id parameter and execute the query
            $stmt->bind_param('i', $claimId);

            if ($stmt->execute()) {
                // On successful deletion, redirect with a success message
                header('Location: view_claims.php?delete_success=1');
                exit();
            } else {
                // Log or display error message if the query fails
                echo "Error deleting claim: " . $stmt->error;
            }
            $stmt->close();
        } else {
            echo "Error preparing statement: " . $conn->error;
        }
    } else {
        echo "Invalid claim ID.";
    }
} else {
    echo "Invalid request or claim ID missing.";
}

// Close the database connection
$conn->close();
?>
