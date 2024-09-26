<?php
include '../../config.php';

// Check if the request is POST and the claim_id is set
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['claim_id'])) {
    $claimId = intval($_POST['claim_id']);
    
    // Prepare and execute the delete query
    $stmt = $conn->prepare("DELETE FROM claimer WHERE id = ?");
    $stmt->bind_param('i', $claimId);

    if ($stmt->execute()) {
        // If the delete is successful, redirect to the view claims page with a success message
        header('Location: view_claims.php?delete_success=1');
    } else {
        // If there's an error, redirect to the view claims page with an error message
        header('Location: view_claims.php?delete_error=1');
    }

    $stmt->close();
} else {
    // If the request is invalid, redirect to the view claims page
    header('Location: view_claims.php');
}

$conn->close();
?>
