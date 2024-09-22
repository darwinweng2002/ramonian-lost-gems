<?php
include '../config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize and collect form data
    $itemId = intval($_POST['item_id']);
    $itemDescription = htmlspecialchars($_POST['item_description']);
    $dateLost = htmlspecialchars($_POST['date_lost']);
    $locationLost = htmlspecialchars($_POST['location_lost']);
    $securityQuestion = htmlspecialchars($_POST['security_question']);

    // File uploads: Proof of Ownership and Personal ID
    $proofOfOwnership = $_FILES['proof_of_ownership'];
    $personalId = $_FILES['personal_id'];

    // Define directory to upload files
    $uploadDir = '../uploads/claims/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // Upload files
    $ownershipPath = $uploadDir . basename($proofOfOwnership['name']);
    move_uploaded_file($proofOfOwnership['tmp_name'], $ownershipPath);

    $personalIdPath = $uploadDir . basename($personalId['name']);
    move_uploaded_file($personalId['tmp_name'], $personalIdPath);

    // Insert claim data into the database
    $sql = "INSERT INTO claimer (item_id, user_id, item_description, date_lost, location_lost, proof_of_ownership, security_question, personal_id)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('iissssss', $itemId, $_SESSION['user_id'], $itemDescription, $dateLost, $locationLost, $ownershipPath, $securityQuestion, $personalIdPath);
    
    if ($stmt->execute()) {
        echo "Claim submitted successfully!";
    } else {
        echo "Error submitting claim.";
    }

    $stmt->close();
    $conn->close();
}
?>
s