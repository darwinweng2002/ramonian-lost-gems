<?php
include '../config.php';
session_start(); // Ensure session is started

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get the data from the form
    $itemId = intval($_POST['item_id']);
    $itemDescription = htmlspecialchars(trim($_POST['item_description']));
    $dateLost = htmlspecialchars(trim($_POST['date_lost']));
    $locationLost = htmlspecialchars(trim($_POST['location_lost']));
    $securityQuestion = htmlspecialchars(trim($_POST['security_question']));
    
    // Determine if the user is from user_member or user_staff
    if (isset($_SESSION['user_id'])) {
        $claimantId = $_SESSION['user_id']; // For user_member
        $isStaff = false;
    } elseif (isset($_SESSION['staff_id'])) {
        $claimantId = $_SESSION['staff_id']; // For user_staff
        $isStaff = true;
    } else {
        echo "User not logged in!";
        exit;
    }

    // Directory for uploading files
    $uploadDir = '../uploads/claims/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true); // Create directory if it doesn't exist
    }

    // Handle proof of ownership file upload
    $proofOfOwnershipPath = null;
    if (isset($_FILES['proof_of_ownership']) && $_FILES['proof_of_ownership']['error'] == UPLOAD_ERR_OK) {
        $fileName = basename($_FILES['proof_of_ownership']['name']);
        $fileType = $_FILES['proof_of_ownership']['type'];
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];

        if (in_array($fileType, $allowedTypes)) {
            $targetFilePath = $uploadDir . $fileName;
            if (move_uploaded_file($_FILES['proof_of_ownership']['tmp_name'], $targetFilePath)) {
                $proofOfOwnershipPath = $fileName; // Store just the file name in the database
            }
        }
    }

    // Handle personal ID file upload
    $personalIdPath = null;
    if (isset($_FILES['personal_id']) && $_FILES['personal_id']['error'] == UPLOAD_ERR_OK) {
        $fileName = basename($_FILES['personal_id']['name']);
        $fileType = $_FILES['personal_id']['type'];
        if (in_array($fileType, $allowedTypes)) {
            $targetFilePath = $uploadDir . $fileName;
            if (move_uploaded_file($_FILES['personal_id']['tmp_name'], $targetFilePath)) {
                $personalIdPath = $fileName; // Store just the file name in the database
            }
        }
    }

    // Insert the claim into the database
    // Include an extra field `is_staff` to differentiate between user_member and user_staff
    $sql = "INSERT INTO claimer (item_id, user_id, item_description, date_lost, location_lost, proof_of_ownership, security_question, personal_id, is_staff)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('iissssssi', $itemId, $claimantId, $itemDescription, $dateLost, $locationLost, $proofOfOwnershipPath, $securityQuestion, $personalIdPath, $isStaff);

    if ($stmt->execute()) {
        echo "Claim submitted successfully!";
    } else {
        echo "Error submitting claim.";
    }

    $stmt->close();
    $conn->close();
}
?>
