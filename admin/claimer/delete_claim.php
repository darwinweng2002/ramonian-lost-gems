<?php
include '../../config.php';

// Enable error reporting for debugging purposes
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database connection
$conn = new mysqli("localhost", "u450897284_root", "Lfisgemsdb1234", "u450897284_lfis_db");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$response = ['success' => false, 'message' => 'An error occurred.'];

if (isset($_POST['claim_id'])) {
    $claimId = intval($_POST['claim_id']);

    // Prepare the SQL statement to delete the claim
    $stmt = $conn->prepare("DELETE FROM claimer WHERE id = ?");
    $stmt->bind_param('i', $claimId);

    // Check if deletion is successful
    if ($stmt->execute()) {
        $response = ['success' => true, 'message' => 'Claim successfully deleted.'];
    } else {
        $response = ['success' => false, 'message' => 'Failed to delete the claim.'];
    }

    $stmt->close();
}

$conn->close();

// Return JSON response
echo json_encode($response);
